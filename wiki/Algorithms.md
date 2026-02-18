# Core Algorithms & Mathematical Models

StarWeather relies on established aerospace and meteorological models to provide high-fidelity data.

---

## 🛰️ Satellite Orbit Propagation (SGP4)

The system uses the **Simplified General Perturbations (SGP4)** model to propagate the position and velocity of satellites.

### 1. Two-Line Element (TLE) Transformation
The `SatelliteEngine` parses TLE sets provided by NORAD (Celestrak) to extract key Keplerian elements:
- **Inclination ($i$)**: Tilt of the orbit.
- **Eccentricity ($e$)**: Shape of the orbit.
- **RAAN ($\Omega$)**: Right Ascension of the Ascending Node.
- **Argument of Perigee ($\omega$)**.
- **Mean Anomaly ($M$)**.
- **Mean Motion ($n$)**: Revolutions per day.

### 2. Time Propagation
Calculation of time since epoch ($t_{since}$) in minutes:
$$t_{since} = (t_{current} - t_{epoch}) / 60$$

Solving **Kepler's Equation** for Eccentric Anomaly ($E$) using Newton-Raphson iteration:
$$E_{i+1} = M + e \sin(E_i)$$

### 3. Coordinate Conversion (ECI to Geodetic)
After calculating position in the orbital plane, we perform:
- **Orbital Plane to ECI**: Transformation using RAAN and Inclination.
- **ECI to Geodetic**: Accounting for Earth's rotation via **Greenwich Mean Sidereal Time (GMST)** and the Earth's flattening (WGS84 ellipsoidal model).

---

## ⛈️ Storm Detection & Tracking

The `StormTrackingService` identifies atmospheric vortices by analyzing weather metrics in real-time.

### 1. Vortex Identification
The system scans `weather_metrics` for specific threshold combinations:
- **Static Threshold**: Wind Speed ($> 60$ km/h) AND Pressure ($< 1000$ hPa).
- **Proximity Search**: Uses a $2^\circ$ radial buffer to correlate new metrics with existing active storm systems.

### 2. Predictive Path Extrapolation
We use **Linear Extrapolation** (with planned transition to non-linear LSTM) to predict storm movement:
- **Directional Vector**: Calculated from the delta between the two most recent recorded positions.
- **Time Step**: Predicts coordinates at 6-hour intervals up to 30 hours ahead.

---

## 🛰️ Himawari Spectrum Processing

The `HimawariService` synchronizes with the NICT (National Institute of Information and Communications Technology) API.

- **Dynamic Timestamping**: Fetches the `latest.json` to determine the most recent high-resolution scan.
- **Coordination**: Downloads the 1d full-disk 800px sector and maps it onto the 3D globe using UV spherical mapping in Three.js.
