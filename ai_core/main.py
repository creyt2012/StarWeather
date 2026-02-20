from fastapi import FastAPI, UploadFile, File, HTTPException
from pydantic import BaseModel
import numpy as np
import cv2
import os
from datetime import datetime

# HPC C++ Bridge
from hpc_bridge import hpc

# AI Core Pipelines (Level 1 to 3)
from core.pipelines.level1_calibration import run_level1_pipeline
from core.pipelines.level2_inference import run_level2_inference
from core.pipelines.level3_physics import run_level3_physics

app = FastAPI(title="StarWeather AI Core (Deep Learning Edition)")

class CycloneInfo(BaseModel):
    active: bool
    confidence: float
    category_prediction: str
    max_sustained_wind_knots: float

class DeepAnalysisResponse(BaseModel):
    status: str
    temperature_c: float
    pressure_hpa: float
    wind_speed_kmh: float
    cloud_coverage_pct: float
    mean_cloud_top_height_km: float
    cyclone_detection: CycloneInfo
    timestamp: str
    metadata: dict

@app.get("/")
async def root():
    return {
        "name": "StarWeather AI Core", 
        "version": "3.0.0-DEEP-LEARNING", 
        "status": "operational",
        "hpc_engine": "Active (C++)" if hpc else "Inactive",
        "ai_engine": "PyTorch Enabled"
    }

@app.post("/analyze", response_model=DeepAnalysisResponse)
async def analyze_imagery(file: UploadFile = File(...), lat: float = 0.0, lng: float = 0.0):
    try:
        # Load raw bytes to memory
        contents = await file.read()
        nparr = np.frombuffer(contents, np.uint8)
        img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)

        if img is None:
            raise HTTPException(status_code=400, detail="Invalid image payload")

        # -------------------------------------------------------------------
        # 1. Level-1 Processing (Radiometric Calibration & HPC Acceleration)
        # -------------------------------------------------------------------
        gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
        
        # Physics calibration (DN to Radiance to Tb)
        l1_data = run_level1_pipeline(gray)
        
        # HPC Extensions for dense calculations (Optical Flow, Thresholding)
        hpc_metrics = hpc.analyze_image(img) if hpc else {}

        # -------------------------------------------------------------------
        # 2. Level-2 Processing (Deep Learning Inference - UNet/ResNet)
        # -------------------------------------------------------------------
        l2_data = run_level2_inference(img)

        # -------------------------------------------------------------------
        # 3. Level-3 Processing (Atmospheric Physics & Synthesis)
        # -------------------------------------------------------------------
        final_products = run_level3_physics(l1_data, l2_data, hpc_metrics)

        # Build response payload combining DL models and physics
        return DeepAnalysisResponse(
            status="success",
            temperature_c=round(float(np.mean(l1_data["brightness_temp_k"])) - 273.15, 1),
            pressure_hpa=final_products["atmospheric_pressure_hpa"],
            wind_speed_kmh=final_products["wind_speed_kmh"],
            cloud_coverage_pct=l2_data["cloud_mask_pct"],
            mean_cloud_top_height_km=final_products["mean_cloud_top_height_km"],
            cyclone_detection=final_products["cyclone_detection"],
            timestamp=datetime.now().isoformat(),
            metadata={
                "mean_radiance_W_m2": float(np.mean(l1_data["radiance"])),
                "resolution": f"{img.shape[1]}x{img.shape[0]}",
                "ai_network": "UNet+ResNet50",
                "hpc_engine": "C++ High-Performance Computing" if hpc else "Python Fallback"
            }
        )

    except Exception as e:
        import traceback
        traceback.print_exc()
        raise HTTPException(status_code=500, detail=str(e))

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8001)
