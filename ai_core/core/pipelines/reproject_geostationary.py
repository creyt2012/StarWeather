import cv2
import numpy as np
import sys
import argparse
import math

def geostationary_to_equirectangular(input_path, output_path, satellite_lon, out_width=2048, out_height=1024):
    """
    Converts a Geostationary satellite image (e.g. GOES Full Disk) 
    to an Equirectangular (Plate Carrée) projection for 3D sphere mapping.
    """
    # 1. Load the Geostationary Image
    img = cv2.imread(input_path)
    if img is None:
        print(f"Error: Could not read image at {input_path}")
        sys.exit(1)
        
    in_h, in_w = img.shape[:2]
    
    # Constants for GOES ABI Fixed Grid Projection
    # Values based on GOES-R series specifications
    r_eq = 6378137.0  # Earth equatorial radius in meters
    r_pol = 6356752.31414  # Earth polar radius in meters
    h = 35786023.0 + r_eq # Satellite distance from earth center
    
    # 2. Create the output Equirectangular canvas
    out_img = np.zeros((out_height, out_width, 3), dtype=np.uint8)
    
    # 3. Create coordinate grids for the Equirectangular output
    # Longitude runs from -180 to 180
    # Latitude runs from 90 to -90
    lon = np.linspace(-np.pi, np.pi, out_width)
    lat = np.linspace(np.pi/2, -np.pi/2, out_height)
    
    lon_grid, lat_grid = np.meshgrid(lon, lat)
    
    # Adjust longitude relative to satellite sub-point
    sat_lon_rad = np.radians(satellite_lon)
    lon_diff = lon_grid - sat_lon_rad
    
    # Wrap angles to [-pi, pi]
    lon_diff = (lon_diff + np.pi) % (2 * np.pi) - np.pi
    
    # 4. Perform Inverse Projection (Equirectangular -> Geostationary Scanning Angles)
    # Equations based on CGMS LRIT/HRIT Global Specification
    
    c_lat = np.arctan((r_pol**2 / r_eq**2) * np.tan(lat_grid))
    
    r_c = r_pol / np.sqrt(1 - ((r_eq**2 - r_pol**2) / r_eq**2) * np.cos(c_lat)**2)
    s_x = h - r_c * np.cos(c_lat) * np.cos(lon_diff)
    s_y = -r_c * np.cos(c_lat) * np.sin(lon_diff)
    s_z = r_c * np.sin(c_lat)
    
    # Check visibility (is the point on Earth visible from the satellite?)
    # A point is visible if the dot product of the surface normal and the vector to the satellite is positive
    visibility_mask = (s_x * r_c * np.cos(c_lat) * np.cos(lon_diff) + 
                       s_y * r_c * np.cos(c_lat) * np.sin(lon_diff) + 
                       s_z * r_c * np.sin(c_lat) * (r_eq**2 / r_pol**2)) > 0
                       
    # Calculate scanning angles (x, y) in radians
    s1 = s_x**2 + s_y**2 + s_z**2
    x_angle = np.arcsin(-s_y / np.sqrt(s_x**2 + s_y**2 + s_z**2))
    y_angle = np.arctan(s_z / s_x)
    
    # 5. Map Scanning Angles to Image Pixels
    # GOES Full Disk covers roughly +/- 0.1518 radians
    view_angle = 0.151844
    
    # Normalize angles to [0, 1] then scale to image dimensions
    px = (x_angle / view_angle) * (in_w / 2) + (in_w / 2)
    py = -(y_angle / view_angle) * (in_h / 2) + (in_h / 2)
    
    # Create valid pixel mask (within image bounds AND visible)
    valid_px = (px >= 0) & (px < in_w - 1)
    valid_py = (py >= 0) & (py < in_h - 1)
    valid_mask = visibility_mask & valid_px & valid_py
    
    # 6. Remap Pixels using OpenCV (Vectorized for speed)
    # Convert float maps to float32 for cv2.remap
    map_x = np.where(valid_mask, px, -1).astype(np.float32)
    map_y = np.where(valid_mask, py, -1).astype(np.float32)
    
    out_img = cv2.remap(img, map_x, map_y, interpolation=cv2.INTER_LINEAR, borderMode=cv2.BORDER_CONSTANT, borderValue=(0,0,0))
    
    # 7. Add a slight feather/blur to the edge of the disk to blend smoothly with black
    # This prevents the harsh jagged cutoff at the horizon
    disk_mask = np.where(valid_mask, 255, 0).astype(np.uint8)
    # Erode and blur the mask to create an alpha gradient
    kernel = np.ones((7,7), np.uint8)
    eroded_mask = cv2.erode(disk_mask, kernel, iterations=2)
    blurred_mask = cv2.GaussianBlur(eroded_mask, (21, 21), 0)
    
    # Apply alpha blending to smooth out the jagged edges
    out_img = out_img.astype(np.float32)
    alpha = blurred_mask.astype(np.float32) / 255.0
    alpha = np.expand_dims(alpha, axis=-1)
    out_img = out_img * alpha
    
    out_img = out_img.astype(np.uint8)
    
    # 8. Save the Equirectangular Image
    cv2.imwrite(output_path, out_img, [cv2.IMWRITE_JPEG_QUALITY, 90])
    print(f"SUCCESS: Re-projected {input_path} to Equirectangular at {output_path}")

if __name__ == '__main__':
    parser = argparse.ArgumentParser(description='Re-project Geostationary to Equirectangular')
    parser.add_argument('--input', required=True, help='Path to raw GOES/Meteosat image')
    parser.add_argument('--output', required=True, help='Path to output equirectangular image')
    parser.add_argument('--lon', required=True, type=float, help='Sub-satellite longitude (e.g., -75 for GOES-East, 0 for Meteosat)')
    parser.add_argument('--width', default=2048, type=int, help='Output width')
    parser.add_argument('--height', default=1024, type=int, help='Output height')
    
    args = parser.parse_args()
    
    try:
        geostationary_to_equirectangular(args.input, args.output, args.lon, args.width, args.height)
    except Exception as e:
        print(f"FAILED: {str(e)}")
        sys.exit(1)
