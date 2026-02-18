# System Architecture: The Data Fusion Pipeline

StarWeather is engineered as a high-throughput, non-dockerized Laravel 11 ecosystem designed for low-latency meteorological data fusion.

---

## 🏗️ Core Infrastructure

The application bypasses containerization for raw performance, utilizing optimized system-level services:
- **Application Server**: PHP 8.3-FPM with OPcache optimized for high-complexity math.
- **WebSocket Engine**: **Laravel Reverb**, handling real-time satellite position broadcasts with <50ms latency.
- **Data Persistence**: MySQL 8.0 with time-partitioned indexing on weather metric tables.
- **In-Memory Store**: Redis, serving as the L1 cache for current satellite states and queue management.

---

## 🔄 The Data Pipeline (ETL)

We follow a specialized **Extract, Transform, Load (ETL)** pipeline for orbital and weather data:

### 1. Extraction (Ingestion)
- **Norad Jobs**: Daily sync of TLE sets for over 3,000 tracked objects.
- **Himawari Jobs**: 10-minute interval polling of NICT image sectors.
- **IoT/Radar Hooks**: Asynchronous ingestion of ground-station weather metrics.

### 2. Transformation (Fusion)
- **SGP4 Propagation**: TLEs are converted into WGS84 coordinates.
- **Image Rectification**: Full-disk satellite images are sliced and optimized for web delivery.
- **Weighted Assessment**: Metrics are passed through the Risk Engine to generate severity alerts.

### 3. Load (Real-time Delivery)
- **Database**: Permanent record of historical metrics.
- **Redis**: "Hot" state of active satellites.
- **Reverb**: Broadcast to all active client sessions via Socket.io/Echo.

---

## 🚀 Scaling Strategy

- **Queue Priority**: Horizon manages three distinct queues: `satellite` (high frequency), `weather` (batch heavy), and `default`.
- **Stateless Design**: All application logic is stateless, allowing for immediate horizontal scaling by adding more PHP nodes behind a load balancer.
- **Edge Delivery**: Weather assets are streamed directly from local storage with Nginx caching headers to minimize PHP overhead.
