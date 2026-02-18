# Nền tảng Trí tuệ Khí quyển StarWeather

![Mission Control](images/mission_control_terminal.png)

## Tong quan He thong
**StarWeather** là một hệ sinh thái giám sát khí quyển và quỹ đạo cấp cao, kết hợp giữa công nghệ viễn thám vệ tinh (Remote Sensing), trí tuệ nhân tạo (AI Core) và mô hình hóa vật lý thời gian thực. Nền tảng được thiết kế nhằm cung cấp khả năng cảnh báo sớm, phân tích dữ liệu đa phổ và quản lý tài sản không gian cho các tổ chức chuyên nghiệp.

Hệ thống giải quyết các thách thức về **Dữ liệu lớn (Big Data)** trong ngành khí tượng bằng cách hợp nhất các nguồn dữ liệu từ Himawari-8/9, GOES-R Series, và các mạng lưới radar mặt đất vào một giao diện điều khiển 3D duy nhất.

---

## Cau truc Tai lieu (Muc luc)

### [Kien truc He thong](Architecture)
Khám phá thiết kế Hybrid Microservices, luồng dữ liệu (Data Pipeline) từ vệ tinh đến người dùng cuối, và cách hệ thống mở rộng quy mô.

### [Co che Van hanh & Thuat toan](Algorithms)
Phân tích sâu về engine tính toán quỹ đạo SGP4, các thuật toán xử lý ảnh đa phổ và mô hình dự báo AI.

### [He thong Danh gia Rui ro](Risk-Engine)
Tìm hiểu về Condition Engine - bộ não phân tích các chỉ số cực đoan và tự động phát bản tin cảnh báo.

### [Tai lieu API Toan cuc](API-Reference)
Tra cứu danh mục đầy đủ các đầu cuối API (RESTful), bao gồm cả API nội bộ và giao thức kết nối AI Core.

---

## Cong nghe cot loi
- **Backend Core**: Laravel 11 / PHP 8.3 (High-performance API layer).
- **AI Analytics**: FastAPI / Python (Computer Vision & Atmospheric Physics).
- **Real-time Engine**: Laravel Reverb (WebSockets cho telemetry vệ tinh).
- **Visualization**: Three.js / Globe.gl (Mô phỏng địa cầu 3D cường độ cao).
- **Processing**: Redis & Horizon (Quản lý hàng đợi ingest dữ liệu từ CelesTrak & JMA).

---
> [!NOTE]
> Tài liệu này được thiết kế dành cho kỹ sư hệ thống, nhà phân tích dữ liệu và các đối tác tích hợp. Mọi thay đổi về kiến trúc phải được cập nhật tại đây.
