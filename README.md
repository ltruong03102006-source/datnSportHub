<p align="center">
  <a href="https://github.com/your-repo/sporthub" target="_blank">
    <img src="https://img.shields.io/badge/SportHub-Nền%20Tảng%20Thể%20Thao%20Đa%20Năng-059669?style=for-the-badge&logo=sports&logoColor=white" alt="SportHub Logo">
  </a>
</p>

<h1 align="center">⚽ 🎾 🏸 SPORTHUB - HỆ THỐNG ĐẶT SÂN & QUẢN LÝ THỂ THAO TOÀN DIỆN 🏀 🏐 </h1>

<p align="center">
  <b>Giải pháp công nghệ hiện đại giúp kết nối người chơi thể thao và các chủ sân bóng, cầu lông, tennis, pickleball trên toàn quốc.</b>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-v13.x-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel 13">
  <img src="https://img.shields.io/badge/PHP-v8.3+-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP 8.3">
  <img src="https://img.shields.io/badge/TailwindCSS-v4.0-38B2AC?style=flat-square&logo=tailwind-css&logoColor=white" alt="TailwindCSS v4">
  <img src="https://img.shields.io/badge/Alpine.js-v3.x-87C6F1?style=flat-square&logo=alpine.js&logoColor=white" alt="Alpine.js">
  <img src="https://img.shields.io/badge/Vite-v8.0-646CFF?style=flat-square&logo=vite&logoColor=white" alt="Vite">
  <img src="https://img.shields.io/badge/Sanctum-Restful%20API-059669?style=flat-square" alt="Laravel Sanctum">
  <img src="https://img.shields.io/badge/VNPay-Payment%20Gateway-005BAA?style=flat-square" alt="VNPay Integration">
  <img src="https://img.shields.io/badge/License-MIT-green.svg?style=flat-square" alt="License MIT">
</p>

---

## 📌 Tổng Quan Dự Án (Project Overview)

**SportHub** là hệ thống quản lý và đặt sân thể thao đa nền tảng được phát triển nhằm giải quyết bài toán đặt lịch sân thi đấu (Bóng đá, Cầu lông, Tennis, Pickleball, Bóng rổ...) cho người chơi, đồng thời số hóa quy trình vận hành, quản lý doanh thu cho các chủ cụm sân.

Dự án được xây dựng dựa trên kiến trúc **Full-stack Web Application** kết hợp **RESTful APIs cho Mobile/Third-party Integration**, áp dụng các tiêu chuẩn thiết kế UI/UX hiện đại (Glassmorphism, Segmented Role Control, Dark/Light Themes) cùng hệ thống xử lý giao dịch tài chính an toàn.

---

## 🌟 Điểm Nổi Bật Của Hệ Thống (Key Highlights)

- ⚡ **Đặt sân linh hoạt 24/7**: Tìm kiếm sân gần nhất, theo dõi trạng thái ca trống theo thời gian thực (Real-time Slot Visibility) và giữ sân tự động.
- 📦 **Gói đặt sân cố định hàng tuần / tháng**: Hỗ trợ đặt lịch cố định cho các đội bóng, câu lạc bộ với cơ chế tự động chia ca (Booking Package Sessions).
- 💳 **Thanh toán đa phương thức**: Tích hợp cổng thanh toán trực tuyến **VNPay Sandbox** và hệ thống **Ví điện tử nội bộ (Wallet Engine)** cho cả Khách hàng và Chủ sân.
- 🤝 **Cộng đồng & Ghép kèo thi đấu (Matchmaking)**: Tính năng tìm đối thủ, bắt đối ghép sân cho các đội thiếu người chơi.
- 🤖 **Trợ lý ảo AI Chatbot**: Hỗ trợ tư vấn vị trí sân, tra cứu bảng giá và giải đáp thắc mắc người dùng tự động.
- 🧾 **Xuất hóa đơn PDF & Báo cáo số liệu**: Tích hợp DomPDF xuất hóa đơn đặt sân và báo cáo biến động số dư.
- 🔐 **Phân quyền 3 cấp độ (RBAC)**: Phân định rõ ràng giữa **Khách hàng (Customer)**, **Chủ sân (Venue Owner)** và **Quản trị viên (Admin)**.

---

## 👥 Phân Quyền & Tính Năng Chi Tiết (Feature Breakdown)

### 1. 👤 Phân Hệ Khách Hàng (Người Chơi / Customer)
- **Đăng ký & Đăng nhập đa dạng**: Đăng nhập bằng Email/Password, Google OAuth 2.0, Facebook Social Auth (Laravel Socialite).
- **Tìm kiếm sân thông minh**: Lọc sân theo môn thể thao, vị trí địa lý (Tỉnh/Thành, Quận/Huyện, Xã/Phường), mức giá và khoảng cách gần nhất.
- **Quy trình Đặt sân mượt mà**:
  - Chọn ngày, xem khung giờ khả dụng (Time slots).
  - Tự động áp dụng Mã giảm giá (Voucher eligibility engine).
  - Giữ chỗ thời gian thực (Court Locking System) tránh trùng ca.
- **Quản lý Đơn đặt & Đổi lịch (Reschedule & Cancellation)**:
  - Xem lịch sử đặt sân, trạng thái thanh toán.
  - Gửi yêu cầu đổi ca chơi hoặc hủy lịch theo **Chính sách hoàn tiền (Cancellation Policy)**.
- **Ví người dùng (User Wallet)**: Nạp tiền vào ví qua VNPay, rút tiền hoặc sử dụng số dư thanh toán nhanh.
- **Ghép kèo & Đánh giá**: Tìm đội giao hữu, tham gia trận đấu mở và gửi đánh giá (Rating & Reviews) cho từng cụm sân.

---

### 2. 🏟️ Phân Hệ Chủ Sân (Venue Owner)
- **Cổng Đăng nhập & Đăng ký Đối tác chuyên biệt**: Giao diện thiết kế riêng cho đối tác quản lý điểm sân.
- **Quản lý Cụm sân & Sân con (Venues & Courts)**:
  - Cấu hình thông tin cụm sân, địa chỉ, hình ảnh gallery, giấy phép kinh doanh (Legal documents).
  - Quản lý danh sách sân nhỏ (Sân 5 người, 7 người, sân đơn, sân đôi...).
  - Thiết lập giá ca linh hoạt theo khung giờ (Giờ cao điểm / Thấp điểm, Ngày thường / Cuối tuần).
- **Quản lý Lịch đặt & Khách hàng**:
  - Bảng điều khiển (Owner Dashboard) trực quan theo dõi lịch đặt theo ngày/tần suất.
  - Duyệt yêu cầu đổi lịch và hủy ca đặt của khách hàng.
  - Xử lý các gói đặt lịch cố định dài hạn (Venue Packages).
- **Báo cáo Doanh thu & Rút tiền**:
  - Thống kê doanh thu theo thời gian thực.
  - Rút tiền từ Ví Chủ sân (Owner Wallet) về tài khoản ngân hàng cá nhân qua hệ thống duyệt rút tiền.

---

### 3. 🛡️ Phân Hệ Quản Trị Viên (Admin Portal)
- **Tổng quan Hệ thống (System Overview Dashboard)**: Thống kê số lượng cụm sân, khách hàng, tổng doanh thu toàn sàn và hoa hồng dịch vụ.
- **Kiểm duyệt Chủ sân & Điểm sân (Approvals)**:
  - Duyệt yêu cầu đăng ký Chủ sân mới (Owner Registration Approvals) với cơ chế cấp Token thiết lập mật khẩu qua Email.
  - Kiểm duyệt thông tin sân bóng và các thay đổi dữ liệu cụm sân (Venue Update Requests).
- **Quản lý Tài chính & Ví Nền Tảng (Platform Financials)**:
  - Quản lý Ví Nền tảng (Platform Wallet) & khấu trừ phí hoa hồng sàn.
  - Xử lý lệnh rút tiền (Withdrawal Requests) của các chủ sân.
- **Quản lý Danh mục & Voucher**: Quản lý bộ môn thể thao (Sports), Mã giảm giá (Vouchers toàn hệ thống) và cấu hình ứng dụng.

---

## 🛠️ Công Nghệ & Kiến Trúc Sử Dụng (Tech Stack)

### **Backend Core**
- **Framework**: Laravel 13.x (PHP 8.3+)
- **Authentication**: Laravel Sanctum (API Token Auth) & Laravel Socialite (OAuth2 Google/Facebook)
- **Database**: MySQL / MariaDB (với Eloquent ORM & Query Builder)
- **PDF Engine**: Barryvdh DomPDF (Xuất hóa đơn & chứng từ)

### **Frontend & UI/UX**
- **Template Engine**: Blade Templates
- **Styling**: TailwindCSS v4.0 & Vanilla CSS Custom Tokens
- **Interactivity**: Alpine.js (Lightweight reactive JS framework)
- **Build Tool**: Vite 8.0

### **Integrations & Third-party APIs**
- **Thanh toán**: VNPay Payment Gateway Sandbox Integration
- **Trợ lý AI**: AI Chatbot Handler Component

---

## 📐 Kiến Trúc Cơ Sở Dữ Liệu (Database Design)

Hệ thống được thiết kế với hơn **45+ Bảng dữ liệu chuẩn hóa (Normalized Schema)**, đáp ứng khả năng mở rộng (Scalability):

| Nhóm Dữ Liệu | Bảng Chính (Tables) | Mô Tả |
| :--- | :--- | :--- |
| **User & Auth** | `users`, `owner_registrations`, `owner_password_setup_tokens`, `login_histories` | Tài khoản, vai trò, kiểm duyệt chủ sân, nhật ký đăng nhập security. |
| **Venue & Court** | `venues`, `courts`, `sports`, `venue_images`, `venue_legal_documents`, `court_locks` | Quản lý cụm sân, sân con, loại hình thể thao, khóa sân thời gian thực. |
| **Pricing & Slots**| `time_slots`, `slot_prices`, `cancellation_policies` | Cấu hình khung giờ, bảng giá linh hoạt, chính sách hủy sân. |
| **Booking & Package**| `bookings`, `booking_items`, `booking_packages`, `booking_package_sessions`, `booking_reschedule_requests` | Quản lý ca đặt đơn, gói đặt cố định dài hạn, gửi yêu cầu đổi ca chơi. |
| **Wallet & Finance**| `wallets`, `wallet_transactions`, `platform_wallets`, `platform_wallet_transactions`, `withdrawal_requests`, `topup_transactions` | Hệ thống Ví điện tử Khách hàng, Ví Chủ sân, Ví Nền tảng & Rút tiền. |
| **Community & AI** | `match_posts`, `match_participants`, `reviews`, `chatbot_conversations`, `chatbot_messages` | Ghép kèo thi đấu, đánh giá sân và lịch sử trò chuyện AI Chatbot. |

---

## 🚀 Hướng Dẫn Cài Đặt & Chạy Dự Án (Quick Start Guide)

### **Yêu cầu môi trường (Prerequisites)**
- PHP >= 8.3
- Composer >= 2.x
- Node.js >= 18.x & NPM
- MySQL / MariaDB >= 8.0

### **Các bước cài đặt (Step-by-Step Installation)**

1. **Clone dự án & Truy cập thư mục**:
   ```bash
   git clone https://github.com/ltruong03102006-source/datnSportHub.git
   cd dantSportHub
   ```

2. **Cài đặt PHP Dependencies**:
   ```bash
   composer install
   ```

3. **Cài đặt Node Dependencies**:
   ```bash
   npm install
   ```

4. **Cấu hình Môi trường (.env)**:
   Sao chép file `.env.example` thành `.env`:
   ```bash
   Copy-Item .env.example .env
   ```
   Cập nhật thông số kết nối Database trong `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=sporthub_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Tạo App Key & Chạy Migration + Seed Data**:
   ```bash
   php artisan key:generate
   php artisan migrate --seed
   ```

6. **Khởi chạy Server Development**:
   Chạy đồng thời Web Server và Asset Bundler qua lệnh:
   ```bash
   npm run dev
   ```
  ```bash
   php artisan serve
   ```

7. **Truy cập Ứng dụng**:
   Mở trình duyệt tại địa chỉ: `http://127.0.0.1:8000`

---

## 🔐 Tài Khoản Trải Nghiệm Demo (Demo Credentials)

Sau khi chạy lệnh `php artisan db:seed` (bao gồm `TestAccountsSeeder`), bạn có thể thử nghiệm hệ thống với các tài khoản sau:

| Vai Trò (Role) | Email | Mật Khẩu (Password) | Chức Năng Thử Nghiệm |
| :--- | :--- | :--- | :--- |
| **🛡️ Quản trị viên (Admin)** | `admin@gmail.com` | `12345678` | Truy cập `/admin/login`, quản lý duyệt chủ sân, xem báo cáo ví nền tảng. |
| **🏟️ Chủ sân (Venue Owner)** | `owner@gmail.com` | `12345678` | Truy cập `/owner/login`, quản lý danh sách sân bóng, ca đặt & doanh thu. |
| **👤 Khách hàng (User/Player)** | `user@gmail.com` | `12345678` | Truy cập `/login` (đã có sẵn **10.000.000 VNĐ** trong ví để test đặt sân). |

---

## 📄 Tài Liệu API (RESTful API Specifications)

Dự án cung cấp hệ thống API hoàn chỉnh chuẩn **RESTful JSON** được bảo vệ bởi middleware `auth:sanctum`.

### **Auth Endpoints**
- `POST /api/login` - Đăng nhập lấy Bearer Token
- `POST /api/register` - Đăng ký tài khoản người chơi
- `POST /api/owner/login` - Đăng nhập API dành riêng cho Chủ sân

### **Venue & Booking Endpoints**
- `GET /api/venues` - Danh sách cụm sân thể thao (Hỗ trợ lọc & tìm kiếm)
- `GET /api/venues/{id}` - Chi tiết sân & khung giờ khả dụng
- `POST /api/bookings` - Tạo đơn đặt sân mới (Tự động khóa ca & trừ tiền ví/VNPay)
- `GET /api/owner/dashboard` - Báo cáo thống kê dành cho Owner

---

## 🛡️ Tính An Toàn & Tối Ưu Bảo Mật (Security & Best Practices)

- **Database Transactions**: Mọi giao dịch đặt sân, nạp tiền ví, chuyển cọc đều bọc trong `DB::transaction()` đảm bảo tính toàn vẹn dữ liệu (Atomicity).
- **Cơ chế Khóa sân chống trùng ca (Real-time Court Locking)**: Sử dụng cơ chế kiểm tra `court_locks` chống Race Condition khi nhiều người dùng cùng bấm đặt 1 ca đấu tại cùng thời điểm.
- **CSRF & XSS Protection**: Bảo vệ toàn bộ form nhập liệu thông qua CSRF Tokens và Escaping Blade Output.
- **Xác thực API Sanctum**: Mã hóa token truy cập an toàn cho client application.

---

## 👨‍💻 Tác Giả & Liên Hệ (Author & Contact)

- **Dự án**: Đồ án tốt nghiệp / Dự án thực tế **SportHub**
- **Email**: `ltruong03102006@gmail.com`
- **GitHub**: [ltruong03102006-source](https://github.com/ltruong03102006-source)

---

<p align="center">
  <i>Được xây dựng với 💖 và nhiệt huyết lập trình PHP / Laravel. Cảm ơn bạn đã ghé thăm Repository!</i>
</p>
