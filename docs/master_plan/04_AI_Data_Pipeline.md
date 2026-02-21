# CHƯƠNG 4: TRÁI TIM TRÍ TUỆ NHÂN TẠO - AI DATA PIPELINE

Trọng tâm tạo ra doanh thu (Monetization Engine) của hệ thống DeepSky không nằm ở việc truyền dẫn dữ liệu, mà nằm ở Tầng thứ 4: **AI Data Pipeline (Đường ống Trí tuệ Nhân tạo Vệ tinh)**.

Để phân giải một bức ảnh không gian đa phổ (Multispectral Image) từ một vệ tinh địa tĩnh cách trái đất 35,786 km, mắt thường con người là hoàn toàn vô dụng. Mây, tuyết, và khói bụi thường có chung màu trắng trên dải sóng Visible (Ánh sáng Khả kiến). 

Đó là lý do DeepSky xây dựng nên cấu trúc Phân tích L1-L3 theo cấp độ khoa học của NASA Data Processing Levels.

## 4.1. Level-1: Radiometric Calibration (Hiệu chuẩn Bức xạ Cơ bản)
Mọi ma trận điểm ảnh (Pixels) thu về từ vệ tinh thực chất chỉ là Digital Numbers (DN) mang giá trị 8-bit, 10-bit hay 12-bit vô nghĩa. Nhiệm vụ của Level-1 là đánh thức Vật lý học:
- Nhân DN với Hệ số Khuyếch đại (Gain) và Độ lệch (Offset) chuyên biệt từng Vệ tinh.
- Kết quả tạo ra Ma trận năng lượng thực tế (Radiance) - đơn vị W / m² / sr / µm.
- Từ đó, dựa vào Hàm Toán học Bức xạ Planck nghịch đảo (Inverse Planck Function), hệ thống tính toán ra **Nhiệt độ Bức xạ (Brightness Temperature - Tb)**.
- *Insight thu được:* Bất cứ vùng không gian nào có Tb dưới -70 độ C (Xanh lam đậm/Tím), đó không đơn giản là mây, đó là tháp Cumulonimbus (Mây Vũ tích khổng lồ sát tầng Ozon), tiền đề của giông lốc kinh hoàng hoặc siêu bão.

## 4.2. Level-2: Cụm Mạng Nơ-ron Học Sâu (Deep Learning PyTorch)
Đây là phần lõi giá trị nhất của hệ thống, bao gồm 2 Mô hình AI độc lập nhưng hoạt động sóng đôi.

### Mô hình 2.A: Semantic Cloud Segmentation (U-Net)
Hệ thống sử dụng mạng kiến trúc **U-Net** kinh điển trong Y tế (trước đây dùng dò tìm tế bào ung thư ở các lát cắt vĩ mô). DeepSky mang U-Net lên Không gian.
- *Input:* Bức ảnh Trái đất qua 3 phổ màu.
- *Output:* Dấu mặt nạ cấp pixel (Pixel-perfect Mask). Phân lập tuyệt đối ranh giới: Khói Dầu, Tuyết, Lục địa, Đại dương, và Đám mây. Từ đó tính ra **Độ dày Quang học (Optical Thickness)**, quyết định khu vực nào mưa vừa, khu vực nào sắp ngập mặn.

### Mô hình 2.B: Cyclone Object Detection (ResNet50 + SPP)
Hệ thống sử dụng xương sống **ResNet** khổng lồ kết hợp Tính năng Gộp Đa Không Gian (Spatial Pyramid Pooling) cho phép mô hình:
- Trích xuất hàng ngàn Cấu trúc xoắn ốc (Spiral Structures).
- Dự đoán tỷ lệ % hình thành mắt bão (Cyclogenesis).
- Trọng yếu nhất: Hồi quy Toán học (Intensity Regression) để ước lượng sức gió theo đơn vị Knots. Mô hình AI của chúng ta dự đoán một siêu bão vừa hình thành sẽ mạnh Cat-3 hay Cat-5 ngay lúc nó mới chỉ là áp thấp nhiệt đới trên biển Thái Bình Dương.

## 4.3. Level-3: Mô hình Vật lý Khí quyển & Bề mặt (Geophysical Translation)
Tầng cuối cùng kết hợp L1 (Vật lý cơ bản) và L2 (Trí tuệ nhân tạo) để cho ra các Tham số Sản phẩm Cuối (Final Products) phục vụ 6 ngành. 

Ví dụ kinh điển: Hệ thống sử dụng phương trình Lapse Rate (Suy giảm nhiệt độ lên cao, khoảng -6.5°C mỗi km) áp vào Mức chênh lệch giữa Nhiệt độ Đỉnh Mây (Từ L1) và Nhiệt độ Bề mặt Đất để tìm ra **Chiều cao Đỉnh mây (Cloud Top Height)** với độ chính xác theo mét.
Bức tranh Toàn cảnh được hoàn thiện, và dữ liệu JSON này được đẩy vào Database phục vụ API Truy Xuất.

---

## 4.4. Đỉnh Cao Mã Nguồn Mở: Liên Minh Cải Tiến AI (Federated AI Hub)
Không một tập đoàn nào có thể huấn luyện AI hiểu mọi địa hình thế giới. Mô hình nhận diện Bão ở vịnh Mexico sẽ lệch lạc nếu đem dự báo Bão khu vực Biển Đông. 
Với triết lý Open Source, **DeepSky mở toàn bộ bộ tạ (Weights)** và thuật toán L2. Các nhà nghiên cứu AI tại Đại học Quốc gia Hà Nội có thể fine-tune (tinh chỉnh) mô hình cho đặc thù của miền Trung, lưu phiên bản đó lại, và đóng góp (Pull Request) lên hệ thống nhánh chính.

Chỉ sau 2-3 năm, thay vì một mô hình Phương Tây áp đặt, chúng ta có một Trung tâm Liên Minh AI (Federated AI) từ hàng trăm chuyên gia trên toàn cầu, làm mô hình ngày một trở nên thông thái và vô giá.
