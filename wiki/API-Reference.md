# API Reference (V1 Full Catalog)

Hệ thống StarWeather cung cấp các giao diện lập trình ứng dụng (API) chuẩn RESTful. Dưới đây là danh mục chi tiết toàn bộ các đầu cuối (endpoints) hiện có.

## [AUTH] Xác thực (Authentication)

Yêu cầu Header `X-API-KEY`. Đối với các API Internal, sử dụng tham số `?token=`.

---

## [LIVE] Trạng thái Hệ thống (Live State)

| Phương thức | Endpoint | Mô tả |
| :--- | :--- | :--- |
| `GET` | `/api/v1/live/state` | Trạng thái tổng quát của mạng lưới cảm biến và vệ tinh. |
| `GET` | `/api/v1/health` | Kiểm tra trạng thái sẵn sàng (Liveness check). |
| `GET` | `/api/v1/health/system`| Chỉ số chi tiết về hạ tầng (DB, Redis, RAM). |

---

## [SAT] Vệ tinh & Quỹ đạo (Satellites)

| Phương thức | Endpoint | Mô tả |
| :--- | :--- | :--- |
| `GET` | `/api/v1/satellites/live` | Danh sách toàn bộ vệ tinh và vị trí hiện tại. |
| `GET` | `/api/v1/satellites/conjunctions` | Cảnh báo các điểm giao cắt quỹ đạo nguy hiểm. |
| `GET` | `/api/v1/satellites/{id}/telemetry` | Dữ liệu viễn thám thời gian thực của 1 vệ tinh. |
| `GET` | `/api/v1/satellites/imagery-history` | Lịch sử ảnh chụp từ vệ tinh (Time-machine). |
| `GET` | `/api/v1/satellites/{id}/tle` | Dữ liệu TLE (Two-Line Element) thô của vệ tinh. |

---

## [MET] Khí tượng & Dự báo (Weather)

| Phương thức | Endpoint | Mô tả |
| :--- | :--- | :--- |
| `GET` | `/api/v1/weather/latest` | Chỉ số khí tượng mới nhất từ sensor gần nhất. |
| `GET` | `/api/v1/weather/metrics` | Truy vấn dữ liệu lịch sử theo thời gian. |
| `GET` | `/api/v1/weather/ground-stations`| Danh sách và trạng thái các trạm mặt đất. |
| `GET` | `/api/v1/weather/history` | Lịch sử khí tượng chi tiết tại một tọa độ. |
| `GET` | `/api/v1/weather/heatmap` | Dữ liệu mật độ phân bổ cho bản đồ nhiệt. |
| `GET` | `/api/v1/weather/forecast` | Dự báo AI cho 48 giờ tới (Hourly). |
| `GET` | `/api/v1/weather/point-info` | Phân tích sâu tại một điểm (SST, AQI, UV). |
| `GET` | `/api/v1/weather/trends` | Xu hướng biến đổi khí hậu trong 30 ngày qua. |

---

## [STORM] Theo dõi Thiên tai (Storms & Risk)

| Phương thức | Endpoint | Mô tả |
| :--- | :--- | :--- |
| `GET` | `/api/v1/weather/storms` | Danh sách các áp thấp và bão đang hoạt động. |
| `GET` | `/api/v1/weather/storms/{id}` | Thông số chi tiết về sức gió, lộ trình bão. |
| `GET` | `/api/v1/weather/storms/{id}/vortex`| Phân tích cấu trúc lõi và mắt bão. |
| `GET` | `/api/v1/weather/risk-areas` | Các khu vực nằm trong vùng cảnh báo đỏ. |

---

## [ALRT] Logic Cảnh báo (Alerts)

| Phương thức | Endpoint | Mô tả |
| :--- | :--- | :--- |
| `GET` | `/api/v1/alerts/rules` | Danh sách các quy tắc cảnh báo (Condition Engine). |
| `POST`| `/api/v1/alerts/rules` | Tạo mới một quy tắc logic cảnh báo. |
| `GET` | `/api/v1/alerts/history` | Nhật ký các thông báo đã gửi cho người dùng. |

---

## [OPS] Quản lý Nhiệm vụ (Mission Control)

| Phương thức | Endpoint | Mô tả |
| :--- | :--- | :--- |
| `GET` | `/api/v1/mission-control/files` | Quản lý tệp tin truyền từ vệ tinh về trạm. |
| `POST`| `/api/v1/mission-control/upload`| Tải tệp tin lên trung tâm dữ liệu. |
| `GET` | `/api/v1/reports` | Kho báo cáo khoa học định kỳ (PDF/JSON). |
| `GET` | `/api/v1/reports/{file}/download`| Tải xuống báo cáo chi tiết. |

---

## [FIN] Thanh toán & Hàng hải (Billing & Marine)

| Phương thức | Endpoint | Mô tả |
| :--- | :--- | :--- |
| `GET` | `/api/v1/marine/vessels` | Theo dõi tàu thuyền tích hợp dữ liệu AIS. |
| `GET` | `/api/v1/plans` | Thông tin các gói PRO/Enterprise. |
| `POST`| `/api/v1/payments/checkout` | Khởi tạo giao dịch nâng cấp tài khoản. |

---

## [INT] API Bản đồ Chiến thuật (Internal Map)

| Endpoint | Mô tả | Tham số |
| :--- | :--- | :--- |
| `/api/internal-map/satellites` | Stream dữ liệu vệ tinh tốc độ cao. | `token` |
| `/api/internal-map/ground-stations`| Render trạm mặt đất. | `token` |
| `/api/internal-map/storms` | Overlay bão thời gian thực. | `token` |
| `/api/internal-map/point-info` | Thông tin điểm click trên Globe. | `lat`, `lng`, `token` |
| `/api/internal-map/forecast` | Forecast cho Meteogram dashboard. | `lat`, `lng`, `token` |

---

## [AI] Microservice AI Core (:8001)

| Phương thức | Endpoint | Mô tả |
| :--- | :--- | :--- |
| `POST` | `/analyze` | Phân tích spectral hình ảnh vệ tinh. |
| `GET` | `/` | Liveness & Heartbeat của AI Core. |

---
[[Về Trang Chủ](Home)] | [[Ki kiến Trúc](Architecture)]
