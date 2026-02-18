# 🌌 StarWeather (Vệ Tinh)
### Hệ Thống Theo Dõi Vệ Tinh & Trí Tuệ Khí Tượng Cấp Doanh Nghiệp

![Tái hiện địa cầu 3D StarWeather](public/assets/docs/images/globe_visualization.png)

[![Laravel 11](https://img.shields.io/badge/Laravel-11.x-FF2D20?logo=laravel)](https://laravel.com)
[![Vue 3](https://img.shields.io/badge/Vue-3.x-4FC08D?logo=vue.js)](https://vuejs.org)
[![Three.js](https://img.shields.io/badge/Engine-Three.js-black?logo=three.js)](https://threejs.org)
[![SGP4](https://img.shields.io/badge/Algorithm-SGP4-blue)](https://en.wikipedia.org/wiki/Simplified_perturbations_models)

**StarWeather** là một hệ thống trí tuệ thời tiết tinh vi, được thiết kế để xóa nhòa khoảng cách giữa độ chính xác của quỹ đạo vệ tinh và an toàn mặt đất. Bằng cách kết hợp dữ liệu đo xa vệ tinh thời gian thực với dữ liệu khí tượng đa phổ, StarWeather cung cấp những hiểu biết sâu sắc, có thể thực hiện được về các rủi ro khí quyển và hậu cần vệ tinh.

---

## 🚀 Khả Năng Cốt Lõi & Thuật Toán Chuyên Sâu

### 📡 Cơ Học Quỹ Đạo & Theo Dõi Vệ Tinh Độ Chính Xác Cao
Hệ thống sử dụng các mô hình hàng không vũ trụ tiêu chuẩn để dự báo vị trí vật thể trên không gian:
- **Engine Lan Truyền SGP4**: Triển khai mô hình *Simplified General Perturbations (SGP4)* để dự đoán quỹ đạo vệ tinh (ISS, Starlink, Himawari) dựa trên bộ dữ liệu TLE (Two-Line Element).
- **Tính Toán Vận Tốc Tức Thời**: Sử dụng phương trình **Vis-Viva**: $v = \sqrt{\mu (2/r - 1/a)}$, trong đó $\mu$ là hằng số trọng trường của Trái đất ($398600.44 \text{ km}^3/\text{s}^2$), giúp cập nhật tốc độ vệ tinh chính xác theo từng giây.
- **Bù Trừ Chuyển Động Quay Trái Đất**: Tính toán **Thời Gian Sidereal Trung Bình tại Greenwich (GMST)** để chuyển đổi tọa độ Inertial (ECI) sang tọa độ Địa lý (Lat/Lng), bù đắp cho vận tốc quay tự thân của Trái đất ($\approx 15.041^\circ/\text{giờ}$).

### ⛈️ Trí Tuệ Khí Tượng & Xử Lý Ảnh Đa Phổ
StarWeather không chỉ hiển thị ảnh, mà còn phân tích sâu vào các lớp dữ liệu:
- **Xử Lý Phổ Himawari-9**: Thu thập và xử lý các dải phổ hồng ngoại (IR) và ánh sáng khả kiến (VIS). Thuật toán **Chuẩn Hóa Phổ** giúp phân biệt giữa mây băng tầng cao (nhiệt độ thấp) và hơi nước tầng thấp.
- **Đồng Bộ Radar RainViewer**: Tích hợp hệ thống Mosaic Tiles chuẩn XYZ, cho phép chồng lớp dữ liệu radar lượng mưa thời gian thực lên bản đồ địa cầu với độ trễ cực thấp.
- **Nhận Dạng Cấu Trúc Xoáy**: Tự động quét các chỉ số áp suất và vận tốc gió để phát hiện áp thấp nhiệt đới và bão. Sử dụng **Nội Suy Tuyến Tính** để dự báo quỹ đạo bão trong 30 giờ kế tiếp.

### 🛡️ Hệ Thống Kiểm Soát Chất Lượng & QA/QC Dữ Liệu
Để đảm bảo dữ liệu không bị sai lệch do cảm biến lỗi:
- **Kiểm Tra Tính Nhất Quán Không Gian (Spatial Consistency)**: So sánh dữ liệu của một trạm khí tượng với các trạm lân cận. Nếu độ lệch nhiệt độ $> 5^\circ\text{C}$ hoặc áp suất $> 3\text{hPa}$, dữ liệu sẽ bị gắn cờ nghi vấn.
- **Rào Cản Logic vật Lý**: Tự động loại bỏ các điểm dữ liệu phi lý (ví dụ: có mưa nhưng độ ẩm $< 30\%$) thông qua các bộ lọc QAQC chuyên sâu.

### ⚠️ Engine Đánh Giá Rủi Ro Thông Minh
- **Mô Hình Điểm Trọng Số**: Tính toán mức độ rủi ro (0-100) dựa trên mật độ mây, cường độ mưa và biến động áp suất.
- **Điểm Tin Cậy (Confidence Score)**: Đi kèm với mỗi cảnh báo, được tính toán dựa trên độ mới của dữ liệu ($F = e^{- \lambda \cdot T}$) và sự đồng thuận giữa các nguồn dữ liệu (Consensus).

![Bảng điều khiển Trung tâm Nhiệm vụ StarWeather](public/assets/docs/images/dashboard_mockup.png)

---

## 🛠️ Công Nghệ Sử Dụng

| Lớp (Layer) | Công Nghệ & Thuật Toán |
|---|---|
| **Core Engine** | PHP 8.2+ (Optimized FPM), Laravel 11 |
| **Space Math** | SGP4 Core, WGS84 Reference Frame, Vis-Viva Dynamics |
| **Xử Lý Ảnh** | Multi-spectral Normalization, UV Spherical Mapping |
| **Frontend** | Vue 3, Inertia.js, Tailwind CSS |
| **Đồ Họa** | Three.js, Globe.gl (Khối cầu WGS84) |
| **Real-time** | Laravel Reverb (WebSocket), Redis (L1 Cache) |

---

## 📦 Cài Đặt & Triển Khai

### Yêu Cầu Hệ Thống
- PHP 8.2+ & Composer
- Node.js 18+ & NPM
- MySQL 8+ & Redis

### Các Bước Thực Hiện
```bash
# 1. Clone và Cài đặt
git clone https://github.com/creyt2012/vetinh.git
cd vetinh
composer install
npm install

# 2. Cấu hình Môi trường
cp .env.example .env
php artisan key:generate

# 3. Khởi tạo Cơ sở dữ liệu & Dữ liệu mẫu
php artisan migrate --seed

# 4. Chạy Môi trường Phát triển
npm run dev
```

---

## 📖 Tài Liệu Kỹ Thuật (Wiki)

Các bài viết chuyên sâu có sẵn trong Wiki nội bộ:
- [Kiến trúc Hệ thống (System Architecture)](wiki/Architecture.md)
- [Thuật toán Toán học chi tiết (SGP4 & Storm Tracking)](wiki/Algorithms.md)
- [Phương pháp Tính điểm Rủi ro (Risk Scoring)](wiki/Risk-Engine.md)
- [Tài liệu tham khảo API (API Reference)](wiki/API-Reference.md)

---

**Được phát triển với niềm đam mê dành cho Khoa học Trái đất**  
*Cung cấp sức mạnh cho các quyết định dựa trên dữ liệu thông qua trí tuệ quỹ đạo và khí quyển.*
