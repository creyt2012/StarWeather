# Thuật Toán Cốt Lõi & Mô Hình Toán Học

Hệ thống StarWeather dựa trên các mô hình hàng không vũ trụ và khí tượng đã được thiết kế để cung cấp dữ liệu với độ trung thực cao.

---

## 🛰️ Propagate Quỹ Đạo Vệ Tinh (SGP4) & Động Lực Học Quỹ Đạo

Hệ thống sử dụng mô hình **Simplified General Perturbations (SGP4)** để dự báo vị trí và vận tốc của vệ tinh.

### 1. Engine Lan Truyền SGP4
`SatelliteEngine` phân tích các bộ TLE để trích xuất các phần tử Keplerian:
- **Mean Motion ($n$)**: Được chuyển đổi từ số vòng/ngày sang rad/phút ($n = \text{meanMotion} \cdot 2\pi / 1440$).
- **Bán trục lớn ($a$)**: Được suy ra từ Định luật thứ ba của Kepler $a = (\mu / n^2)^{1/3}$.

### 2. Tính Toán Vận Tốc Tức Thời
Chúng tôi tính toán vận tốc quỹ đạo dựa trên phương trình **Vis-Viva**, cho phép cập nhật dữ liệu đo xa theo thời gian thực:
$$v = \sqrt{\mu \left(2/r - 1/a \right)}$$
trong đó $r$ là độ lớn của vectơ vị trí.

### 3. Quay Trái Đất & Chuyển Đổi Địa Lý (GMST)
Để lập bản đồ vệ tinh chính xác trên các trạm mặt đất, chúng tôi tính toán **Thời gian Sidereal Trung bình tại Greenwich (GMST)**:
$$GMST = 280.46061837 + 360.98564736629 \cdot (JD - 2451545.0)$$
Điều này đảm bảo kinh độ $\lambda$ tính đến vòng quay của Trái đất so với RAAN quỹ đạo.

---

## 🌩️ Xử Lý Khí Tượng Đa Phổ & Dữ Liệu Radar

### 1. Hợp Nhất Phổ Himawari IR/VIS
`HimawariService` đồng bộ hóa các dải phổ từ API động của NICT.
- **Chuẩn Hóa Động (Dynamic Normalization)**: Dữ liệu pixel thô được xử lý để phân biệt giữa mây băng tầng cao (nhiệt độ thấp) và hơi nước.
- **UV Spherical Mapping**: Hình ảnh được ánh xạ lên một khối ellipsoid WGS84 trong Three.js bằng tọa độ UV tiêu chuẩn, đảm bảo không bị biến dạng tại xích đạo.

### 2. Mosaic Radar XYZ
Dữ liệu từ **RainViewer** được xử lý thông qua hệ thống phân mảnh (tiling) XYZ. Điều này cho phép hệ thống tải chính xác theo vùng nhìn của người dùng, giảm tải băng thông và tăng tốc độ hiển thị các lớp lượng mưa.

---

## ⛈️ Phát Hiện Bão & Dự Báo Quỹ Đạo

### 1. Nhận Dạng Cấu Trúc Xoáy (Vortex Identification)
`StormTrackingService` xác định các lốc xoáy khí quyển bằng cách phân tích các số liệu thời tiết trong thời gian thực:
- Quét các ngưỡng gió $> 60$ km/h và áp suất $< 1000$ hPa.
- Sử dụng thuật toán **Tìm kiếm Vùng lân cận (Proximity Search)** với bán kính $2^\circ$ để liên kết dữ liệu mới với các cơn bão đang hoạt động.

### 2. Dự Báo Quỹ Đạo (Path Extrapolation)
Sử dụng vectơ tuyến tính dựa trên 2 điểm quan sát gần nhất để dự đoán tọa độ trong các khoảng thời gian 6 giờ:
$$\vec{P}_{next} = \vec{P}_{last} + (\vec{P}_{last} - \vec{P}_{prev}) \cdot \Delta t$$

---

## 🛡️ Kiểm Soát Chất Lượng (QA/QC)

Mỗi điểm dữ liệu trước khi được đưa vào Risk Engine phải trải qua bộ lọc **QAQCProcessor**:
- **Spatial Consistency**: So sánh trạm hiện tại với trung bình của $N$ trạm lân cận.
- **Range Constraint**: Nhiệt độ phải nằm trong khoảng $[-80, 60]^\circ\text{C}$ và áp suất $[800, 1100]\text{hPa}$.
