# PNJ PRO - OpenCart 4 Theme & Extension (dc_minimal)

Dự án này là một bộ giao diện (Theme) cao cấp dành riêng cho **OpenCart 4**, được tinh chỉnh sâu và phát triển dựa trên giao diện lõi `dc_minimal`. Mục tiêu của dự án là thiết kế lại toàn diện Front-end (UI/UX) theo phong cách sang trọng, hiện đại và tối giản, tập trung vào việc mô phỏng và nâng cấp trải nghiệm người dùng tương tự như chuỗi bán lẻ trang sức cao cấp **PNJ**.

---

## 🌟 Tính Năng Cốt Lõi (Core Features)

### 1. Hệ Thống Mega Menu Động (Dynamic Mega Menu)
Thay vì sử dụng menu thả xuống (dropdown) truyền thống của OpenCart, dự án đã xây dựng một hệ thống Mega Menu mạnh mẽ xử lý lượng lớn danh mục và nội dung.
- **Cấu trúc Menu phân tần:** Chia các luồng chính như "Nam", "Nữ", "Trẻ em" cùng các Sub-category trực quan.
- **Tích hợp Modal Layout:** Khi hover/click vào các danh mục chính, một khung Modal lớn sẽ xuất hiện hiển thị song song danh sách **thương hiệu (brands)**, **bộ sưu tập (collections)** và các **banner quảng cáo**.
- **UX/UI mượt mà:** Xử lý hiệu ứng transition, giải quyết các rủi ro conflict giữa Mega Menu Modal và Search Modal trên Mobile & Desktop.
- **File liên quan:** 
  - `catalog/view/template/common/menu.twig`
  - `catalog/view/template/common/header.twig`
  - `catalog/view/stylesheet/menu.css`

### 2. PNJ Collection 3D Slider (Module dc_collection)
Module tùy chỉnh hoàn toàn trình chiếu (Slider) cho Trang chủ, sử dụng thư viện Swiper.js v11 mạnh mẽ nhất.
- **Hiệu ứng 3D Coverflow:** Các banner Collection được hiển thị dạng vòng tuần hoàn (Circular Infinity Loop) với hiệu ứng 3D, trong đó ảnh chính giữa hiển thị 100%, ảnh 2 bên bị đẩy lùi (depth) và làm mờ (opacity), chồng lấp lên nhau theo khuôn mẫu chính xác của PNJ.
- **Tương tác đồng bộ kép (2-Way Sync):** Giao diện được thiết kế chia làm 2 phần (Phần trên là banner, phần dưới là danh sách sản phẩm thuộc banner đó). Khi vuốt hoặc click chuyển banner, Swiper ở dưới tự động thay đổi danh sách sản phẩm tướng ứng (seamless sync) không có độ trễ.
- **Tối ưu hiển thị:** Nút Navigation `Next` và `Prev` được đẩy lùi ra 2 lề ngoài cùng (mép màn hình) để không che khuất hình ảnh nội dung.
- **File liên quan:** 
  - `catalog/controller/module/dc_collection.php`
  - `catalog/view/template/module/dc_collection.twig`

### 3. Top Banner Quảng Cáo (Module dc_top_banner)
Hệ thống quản lý Banner ngang ở đỉnh trang web.
- **Khả năng Bật/Tắt:** Quản trị viên (Admin) có thể tải ảnh lên, chèn link và bật/tắt banner trực tiếp tại Dashboard.
- **Nút Đóng (Close Button) thân thiện:** Nút tắt (X) được đặt sát mép rìa ngoài cùng bên phải, không đè lên nội dung banner. Nếu người dùng tắt, banner sẽ thu gọn mượt mà.
- **File liên quan:** 
  - `catalog/view/template/module/dc_top_banner.twig`

### 4. Custom Price Filter Logic (Bộ Lọc Giá Nâng Cao)
Giải quyết triệt để lỗi thiết kế bộ lọc giá mặc định của OpenCart khi trang web sử dụng nhiều cấu trúc giá (Price, Discount, Special).
- **Sử dụng OpenCart Event System:** Gắn hook vào sự kiện `catalog/model/catalog/product/getProducts/before`.
- **SQL Injection Safety & Precise Query:** Viết lại câu lệnh SQL để OpenCart khi lọc giá sẽ ưu tiên giá đã giảm (Special Price) nếu có. 
  - *Ví dụ:* Nếu cấu hình lọc sản phẩm từ 200,000đ - 300,000đ, nó sẽ hiểu để lấy sản phẩm *có giá gốc 500,000đ nhưng đang Sale còn 250,000đ*.
- **File liên quan:** 
  - `catalog/model/module/filter.php` (Custom Model cho logic filter)

### 5. Giao Diện In Mã QR Chuyên Biệt (QR Print Isolation Layout)
- **CSS Media Print:** Khi nhân viên hoặc người dùng bấm `Ctrl + P`, toàn bộ giao diện web (Header, Footer, Button, Banner, Sidebars) sẽ bị ẩn hoàn toàn (display: none).
- Chỉ duy trì duy nhất khung chứa **QR Code** và thông tin text thiết yếu, thiết lập mặc định khổ in ngang (Landscape) và tỉ lệ tràn lề tối ưu cho máy in mã vạch.

### 6. Bản Địa Hoá (Localization) Tiếng Việt & VNĐ
- Cấu hình hoàn thiện gói ngôn ngữ Tiếng Việt và các cấu trúc biến.
- Khớp định dạng tiền tệ VNĐ trên Front-end (₫XXX,XXX) thay vì mặc định của hệ thống.
- Thiết lập nhận diện thư mục Admin tùy chỉnh (nếu dùng `adminpnj` hoặc tương tự) để không gây lỗi load file ngôn ngữ.

---

## 📂 Kiến Trúc & Cấu Trúc Thư Mục (Directory Structure)

Dự án tuân thủ cấu trúc của một `Extension` trong OpenCart 4, nằm gọn tại `extension/dc_minimal/`.

```text
extension/dc_minimal/
├── admin/                        # Chứa logic backend cho quản trị viên (Admin Panel)
│   ├── controller/module/        # Các controller điều khiển Setting form cho Module.
│   ├── language/en-gb/module/    # File ngôn ngữ riêng biệt trong Backend.
│   └── view/template/module/     # Các file Twig hiển thị form cấu hình trong Backend.
│
├── catalog/                      # Chứa hiển thị Front-end cho Khách hàng
│   ├── controller/               # (startup/, module/) Nơi render dữ liệu từ MVC.
│   ├── model/module/             # Quản lý Database query (ví dụ: Custom Filter SQL).
│   ├── view/
│   │   ├── stylesheet/           # Thư mục chứa CSS tùy chỉnh (theme.css, header.css, menu.css).
│   │   └── template/             # Layout chính của giao diện (header, footer, category, product, module).
│
├── install.json                  # File quan trọng định nghĩa Theme, Ver, và Register Events khi cài đặt.
└── README.md                     # File hướng dẫn (bạn đang đọc).
```

---

## ⚙️ Hướng Dẫn Cài Đặt (Installation Details)

### Bước 1: Upload Mã Nguồn
Bạn tải về/Copy toàn bộ lõi thư mục dự án và thả vào thư mục `extension/` của máy chủ:
- Đường dẫn đích: `[thư mục opencart_của_bạn]/extension/dc_minimal`

### Bước 2: Kích Hoạt Theme
1. Đăng nhập vào **OpenCart Admin Panel**.
2. Điều hướng tới `Extensions` -> `Extensions`.
3. Trong ô dropdown *Choose the extension type*, chọn **Themes**.
4. Tìm đến dòng **Design Cart Minimal 4 Theme**, ấn nút **Install** (màu xanh lá) và sau đó ấn mũi tên sửa (**Edit**).
5. Đặt Status thành **Enabled** và tuỳ chỉnh kích thước ảnh nếu cần. Lưu lại.

### Bước 3: Đăng Ký Event Hooks (Rất Quan Trọng)
Dự án sử dụng các Event (nêu trong `install.json`) để thực thi Custom Filter. Nếu bạn upload qua FTP, Events sẽ không được tự động đăng ký vào database.
- Tới `System` -> `Design` -> `Theme Editor` hoặc dùng **Extension Installer** cài 1 file zip nhỏ chứa `install.json` để kích hoạt event.
- Hoặc có thể tới **Extensions** -> **Events**, bật các Event thuộc `extension/dc_minimal/module/filter|beforeGetProducts`.

### Bước 4: Kích Hoạt Các Module Con Độc Lập
1. Tới `Extensions` -> `Extensions` -> Chọn type **Modules**.
2. Lần lượt Cài đặt (Install) và Bật (Enable) các module sau:
   - **DC Collection** (Trình chiếu Bộ sưu tập 3D) -> Vào config -> Thêm ảnh, tạo collection, và Save.
   - **DC Top Banner** (Banner đỉnh trang) -> Upload ảnh banner, kích hoạt.

### Bước 5: Cấp Phép System Design
1. Tới `Design` -> `Layouts`.
2. Gắn module **DC Top Banner** vào layout `Home` ở vị trí (Position) mong muốn (Ví dụ: `Content Top`).
3. Làm tương tự cho **DC Collection**.

### Bước 6: Xóa Cache (Mọi thứ sẽ hoạt động!)
- Bấm vào hình bánh răng / **Thùng rác** trên thanh công cụ góc phải cùng của trang Admin (Dashboard).
- Xóa bộ nhớ đệm **Theme** và **SASS**.
- Ra Front-end (trang chủ) bấm `Ctrl + F5` để tận hưởng hệ thống thiết kế mới.

---

## 💻 Tech Stack & Dependencies
- **Core Platform:** OpenCart 4.x
- **Template Engine:** Twig 3.x
- **Styling:** CSS3, CSS Flexbox/Grid, Bootstrap 5 (Core layout).
- **Libraries Front-end:** 
  - jQuery v3.6.x (Đi kèm OpenCart)
  - [SwiperJS v11](https://swiperjs.com/) (Module trình chiếu mượt mà trên Mobile/Desktop).
- **Database:** MySQL/MariaDB (Được tương tác qua đối tượng `$this->db` của OpenCart).
