from fastapi import FastAPI, UploadFile, File, HTTPException
from pydantic import BaseModel
import numpy as np
import cv2
import os
from datetime import datetime

# Import C++ High-Performance Computing Bridge
from hpc_bridge import hpc

app = FastAPI(title="StarWeather AI Core (HPC Enhanced)")

class WeatherAnalysisResponse(BaseModel):
    status: str
    temperature_c: float
    pressure_hpa: float
    wind_speed_kmh: float
    wind_direction_deg: int
    cloud_coverage_pct: float
    timestamp: str
    metadata: dict

@app.get("/")
async def root():
    return {
        "name": "StarWeather AI Core", 
        "version": "2.0.0-HPC", 
        "status": "operational",
        "hpc_engine": "C++ Extension Active" if hpc else "Python/OpenCV Fallback"
    }

@app.post("/analyze", response_model=WeatherAnalysisResponse)
async def analyze_imagery(file: UploadFile = File(...), lat: float = 0.0, lng: float = 0.0):
    try:
        # 1. Read image bytes into Memory (RAM)
        contents = await file.read()
        nparr = np.frombuffer(contents, np.uint8)
        img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)

        if img is None:
            raise HTTPException(status_code=400, detail="Invalid image format")

        # 2. Level-1 Processing (Route to C++ if available, else Python)
        if hpc:
            # ZERO-COPY: Send continuous memory pointer to C++
            hpc_metrics = hpc.analyze_image(img)
            
            # Extract results directly from C++ memory space
            temp_c = hpc_metrics["temperature_c"]
            pressure = hpc_metrics["pressure_hpa"]
            coverage = hpc_metrics["cloud_coverage_pct"]
            mean_brightness = hpc_metrics["mean_brightness"]
            engine = "AI_CORE_V2_CPP_HPC"
        else:
            # FALLBACK: Python / OpenCV Processing
            gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
            mean_brightness = np.mean(gray)
            cloud_pixels = np.sum(gray > 130)
            coverage = (cloud_pixels / gray.size) * 100
            temp_c = round((300 - (mean_brightness / 255) * 100) - 273.15, 1)
            pressure = round(1013.25 - (coverage / 100) * 45, 1)
            engine = "AI_CORE_V1_PYTHON_FALLBACK"

        # 3. Model Derived Physics (e.g. Optical flow mockup)
        wind_speed = round(15 + (coverage / 100) * 20, 1)
        wind_dir = 90 if lat < 30 and lat > -30 else 270

        return WeatherAnalysisResponse(
            status="success",
            temperature_c=temp_c,
            pressure_hpa=pressure,
            wind_speed_kmh=wind_speed,
            wind_direction_deg=wind_dir,
            cloud_coverage_pct=round(coverage, 2),
            timestamp=datetime.now().isoformat(),
            metadata={
                "mean_brightness": float(mean_brightness),
                "resolution": f"{img.shape[1]}x{img.shape[0]}",
                "engine": engine
            }
        )

    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8001)
