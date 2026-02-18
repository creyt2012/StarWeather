# Kiến Trúc Hệ Thống & Chiến Lược Mở Rộng

StarWeather được thiết kế theo kiến trúc **Monolithic-Distributed Hybrid**, tận dụng sức mạnh xử lý tập trung của Laravel đồng thời phân tán các tác vụ nặng thông qua hàng đợi Redis và các Node xử lý riêng biệt.

---

## 🏢 Phân Lớp Kiến Trúc (Architectural Layers)

Hệ thống được tổ chức thành 4 phân lớp logic chính:

### 1. Lớp Ingestion (Thu Thập Dữ Liệu)
Sử dụng các tiến trình daemon chạy ngầm (Laravel Horizon) để duy trì kết nối liên tục với các trạm cung cấp dữ liệu:
- **Orbital Stream**: Kết nối với API của NORAD để lấy dữ liệu TLE mới nhất mỗi 24 giờ.
- **Weather Stream**: Polling dữ liệu ảnh Himawari-9 mỗi 10 phút để đảm bảo tính thời sự của bản đồ mây toàn cầu.

### 2. Lớp Processing & Analytics (Xử Lý & Phân Tích)
Đây là nơi thực thi các thuật toán SGP4 và QAQC:
- **SGP4 Propagator**: Chuyển đổi dữ liệu orbital thành tọa độ địa lý.
- **QA/QC Processor**: Thực hiện kiểm tra tính nhất quán không gian và rào cản vật lý để loại bỏ dữ liệu nhiễu.

### 3. Lớp Distribution (Phân Phối & Real-time)
Dữ liệu sau khi xử lý được đẩy ra ngoài thông qua hai kênh:
- **RESTful API**: Dành cho các bên thứ ba tích hợp dữ liệu.
- **WebSocket (Laravel Reverb)**: Phát sóng trực tiếp vị trí vệ tinh cho hàng ngàn người dùng đồng thời với độ trễ cực thấp.

---

## 🚀 Chiến Lược Mở Rộng (Scaling Strategy)

StarWeather được tối ưu hóa để chạy trên các server vật lý mạnh mẽ mà không cần ảo hóa, giúp giảm overhead và tăng hiệu năng tính toán:

### 1. Phân Cấp Bộ Nhớ Đệm (Multi-level Caching)
- **L1 Cache (Redis)**: Lưu trữ "Hot States" - trạng thái hiện tại của tất cả vệ tinh đang hoạt động để truy xuất tức thì.
- **L2 Cache (Filesystem/CDN)**: Lưu trữ các tệp tin hình ảnh vệ tinh và radar đã qua xử lý.

### 2. Quản Lý Hàng Đợi (Queue Orchestration)
Sử dụng **Laravel Horizon** để giám sát và điều phối hàng trăm Worker. Các tác vụ được phân bổ vào các hàng đợi có ưu tiên khác nhau:
- `high`: Dùng cho các cảnh báo bão và rủi ro khẩn cấp.
- `satellite`: Dùng cho việc tính toán quỹ đạo định kỳ.
- `weather`: Dùng cho việc tải và xử lý ảnh vệ tinh nặng.

### 3. Cấu Trúc Dữ Liệu Lớn (Big Data Handling)
Bảng `weather_metrics` được thiết kế để hỗ trợ **Table Partitioning** theo tháng hoặc năm, cho phép truy vấn dữ liệu lịch sử hàng tỷ bản ghi mà không làm chậm hệ thống.
