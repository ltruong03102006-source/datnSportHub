# 3.7. Sơ đồ hoạt động theo phân quyền

Dự án SportHub sử dụng ba phân quyền chính: **User**, **Owner** và **Admin**. Mỗi phân quyền được hệ thống kiểm tra khi người dùng truy cập chức năng tương ứng. Nếu người dùng chưa đăng nhập, sai vai trò hoặc tài khoản không ở trạng thái hoạt động, hệ thống sẽ từ chối truy cập hoặc chuyển về trang đăng nhập.

## 3.7.1. Phân quyền User

### 3.7.1.1. Chức năng của User

User là khách hàng sử dụng hệ thống để tìm kiếm, đặt sân và quản lý lịch đặt của mình.

Các chức năng chính:

- Đăng ký, đăng nhập, đăng xuất.
- Xem danh sách sân, tìm kiếm sân, xem cơ sở gần đây và bảng xếp hạng.
- Xem chi tiết cơ sở, sân con, khung giờ trống và voucher áp dụng.
- Đặt sân lẻ, đặt gói sân, thanh toán bằng COD, ví hoặc VNPay tùy luồng.
- Xem lịch sử đặt sân, hủy sân, yêu cầu đổi lịch.
- Xem lịch sử giao dịch, nhận thông báo.
- Đánh giá sân sau khi sử dụng, báo cáo sân vi phạm.
- Thêm hoặc bỏ yêu thích cơ sở sân.
- Tham gia cộng đồng tìm người chơi cùng.
- Cập nhật hồ sơ cá nhân, mật khẩu, ảnh đại diện, thông tin ngân hàng.
- Sử dụng chatbot hỗ trợ.

### 3.7.1.2. Các bước hoạt động tổng quát của User

1. User truy cập hệ thống SportHub.
2. Hệ thống hiển thị trang tìm sân, sân gần đây, bảng xếp hạng và chi tiết cơ sở.
3. Nếu User chỉ xem thông tin công khai thì không bắt buộc đăng nhập.
4. Nếu User thực hiện chức năng cá nhân như đặt sân, đặt gói, hủy lịch, đổi lịch, yêu thích, đánh giá hoặc cộng đồng, hệ thống yêu cầu đăng nhập.
5. User nhập thông tin đăng nhập hoặc đăng ký tài khoản.
6. Hệ thống kiểm tra thông tin tài khoản.
7. Nếu thông tin không hợp lệ, hệ thống hiển thị lỗi.
8. Nếu hợp lệ, hệ thống cho phép User chọn chức năng.
9. Với chức năng đặt sân:
   - User chọn cơ sở, sân con, ngày, giờ, dịch vụ đi kèm và voucher nếu có.
   - Hệ thống kiểm tra cơ sở/sân có hoạt động không.
   - Hệ thống kiểm tra khung giờ có còn trống không.
   - Nếu dữ liệu không hợp lệ hoặc khung giờ đã có người đặt, hệ thống hiển thị lỗi.
   - Nếu hợp lệ, hệ thống tính tiền, áp dụng voucher và kiểm tra phương thức thanh toán.
   - Nếu thanh toán bằng ví nhưng số dư không đủ, hệ thống hiển thị lỗi.
   - Nếu thanh toán hợp lệ, hệ thống tạo booking, tạo giao dịch và gửi thông báo.
10. Với chức năng quản lý lịch đặt:
    - User xem lịch sử booking.
    - User có thể hủy booking nếu chưa đến giờ chơi.
    - Hệ thống tính phí hủy theo chính sách của cơ sở và hoàn tiền vào ví nếu đủ điều kiện.
    - User có thể gửi yêu cầu đổi lịch nếu booking đã được xác nhận và còn trước giờ chơi tối thiểu theo quy định.
11. Với chức năng cộng đồng:
    - User tạo bài tìm người chơi, xin tham gia bài viết, duyệt hoặc từ chối người xin tham gia bài viết do mình tạo.
12. Hệ thống cập nhật dữ liệu, hiển thị thông báo thành công hoặc lỗi.
13. Kết thúc luồng.

### 3.7.1.3. Mã Mermaid sơ đồ hoạt động User

```mermaid
flowchart TD
    A((Bắt đầu)) --> B[User truy cập SportHub]
    B --> C{Chọn chức năng công khai?}
    C -- Có --> D[Xem/tìm kiếm sân, sân gần đây, bảng xếp hạng]
    D --> Z((Kết thúc))
    C -- Không --> E{Đã đăng nhập?}
    E -- Không --> F[Đăng nhập hoặc đăng ký]
    F --> G{Thông tin hợp lệ?}
    G -- Không --> H[Hiển thị lỗi]
    H --> F
    G -- Có --> I[Truy cập chức năng User]
    E -- Có --> I

    I --> J{Chọn chức năng}
    J --> K[Đặt sân]
    J --> L[Đặt gói sân]
    J --> M[Quản lý lịch đặt]
    J --> N[Cộng đồng]
    J --> O[Hồ sơ, yêu thích, giao dịch, thông báo, chatbot]

    K --> K1[Chọn cơ sở, sân, ngày giờ, dịch vụ, voucher]
    K1 --> K2{Dữ liệu và sân hợp lệ?}
    K2 -- Không --> H
    K2 -- Có --> K3{Khung giờ còn trống?}
    K3 -- Không --> H
    K3 -- Có --> K4[Tính tiền và chọn thanh toán]
    K4 --> K5{Thanh toán hợp lệ?}
    K5 -- Không --> H
    K5 -- Có --> K6[Tạo booking, giao dịch, thông báo]
    K6 --> Z

    L --> L1[Chọn gói, lịch học/chơi định kỳ]
    L1 --> L2{Gói và lịch hợp lệ?}
    L2 -- Không --> H
    L2 -- Có --> L3[Thanh toán VNPay]
    L3 --> L4{Thanh toán thành công?}
    L4 -- Không --> H
    L4 -- Có --> L5[Kích hoạt gói và sinh lịch đặt]
    L5 --> Z

    M --> M1[Xem lịch sử đặt sân]
    M1 --> M2{Chọn thao tác}
    M2 --> M3[Hủy sân]
    M2 --> M4[Yêu cầu đổi lịch]
    M3 --> M5{Còn được hủy?}
    M5 -- Không --> H
    M5 -- Có --> M6[Tính phí hủy, cập nhật booking, hoàn tiền nếu có]
    M6 --> Z
    M4 --> M7{Booking đủ điều kiện đổi lịch?}
    M7 -- Không --> H
    M7 -- Có --> M8[Gửi yêu cầu đổi lịch cho Owner duyệt]
    M8 --> Z

    N --> N1[Tạo bài hoặc xin tham gia]
    N1 --> N2[Cập nhật trạng thái bài viết/thành viên]
    N2 --> Z

    O --> O1[Cập nhật hoặc xem dữ liệu cá nhân]
    O1 --> Z
```

## 3.7.2. Phân quyền Owner

### 3.7.2.1. Chức năng của Owner

Owner là chủ sân/đối tác quản lý cơ sở sân của mình. Tài khoản Owner phải có vai trò `owner` và trạng thái `active` mới được truy cập khu vực quản lý.

Các chức năng chính:

- Đăng ký làm chủ sân, thiết lập mật khẩu và đăng nhập cổng Owner.
- Xem dashboard tổng quan.
- Quản lý cơ sở sân: thêm cơ sở, xem danh sách, xem chi tiết, sửa thông tin, gửi hồ sơ pháp lý, tạm ngưng hoặc mở lại cơ sở.
- Quản lý sân con: thêm, sửa, xóa, bật/tắt trạng thái.
- Quản lý khung giờ: sinh ca tự động, thêm ca thủ công, cấu hình giá, khóa/mở khóa ca bảo trì.
- Quản lý lịch đặt sân: xem lịch, lọc theo cơ sở/sân/trạng thái, xác nhận hoặc từ chối booking, hủy booking đã xác nhận.
- Xử lý yêu cầu đổi lịch của khách hàng: duyệt hoặc từ chối.
- Quản lý gói đặt sân: bật/tắt đặt gói cho cơ sở, thêm/sửa/xóa/tắt gói.
- Quản lý dịch vụ đi kèm: thêm, sửa, bật/tắt, xóa dịch vụ nếu chưa phát sinh booking.
- Quản lý voucher: tạo, sửa, gia hạn, bật/tắt, xóa, xem báo cáo hiệu quả.
- Quản lý đánh giá: xem đánh giá và phản hồi khách hàng.
- Quản lý ví: xem số dư, nạp tiền, cập nhật ngân hàng, tạo yêu cầu rút tiền.
- Quản lý hợp đồng hợp tác với Admin: xem, tải, ký hoặc từ chối hợp đồng.
- Chuyển nhượng cơ sở cho Owner khác.
- Nhận và đọc thông báo.

### 3.7.2.2. Các bước hoạt động tổng quát của Owner

1. Owner truy cập khu vực quản lý chủ sân.
2. Hệ thống kiểm tra đăng nhập.
3. Hệ thống kiểm tra tài khoản có vai trò Owner và trạng thái active.
4. Nếu không hợp lệ, hệ thống từ chối truy cập hoặc hiển thị lỗi.
5. Nếu hợp lệ, hệ thống hiển thị dashboard Owner.
6. Owner chọn nhóm chức năng cần thao tác.
7. Với chức năng quản lý cơ sở:
   - Owner nhập thông tin cơ sở, địa chỉ, môn thể thao, hình ảnh, hồ sơ pháp lý.
   - Hệ thống kiểm tra dữ liệu.
   - Nếu hợp lệ, hệ thống tạo cơ sở ở trạng thái pending và gửi thông báo cho Admin.
   - Admin duyệt hồ sơ, sau đó cơ sở ở trạng thái approved.
   - Admin gửi hợp đồng; Owner ký hợp đồng.
   - Nếu hợp đồng có hiệu lực, cơ sở chuyển sang active để khách hàng đặt sân.
8. Với chức năng quản lý sân con và ca sân:
   - Owner chọn cơ sở thuộc quyền sở hữu.
   - Hệ thống kiểm tra quyền sở hữu và trạng thái cơ sở.
   - Owner thêm/sửa/xóa sân con hoặc sinh ca/thêm ca.
   - Hệ thống kiểm tra trùng ca và booking tương lai.
   - Nếu hợp lệ, hệ thống cập nhật dữ liệu; nếu không hợp lệ, hệ thống hiển thị lỗi.
9. Với chức năng quản lý lịch đặt:
   - Owner xem danh sách booking trên lịch.
   - Owner chọn booking đang chờ.
   - Owner xác nhận hoặc từ chối booking.
   - Nếu xác nhận, hệ thống kiểm tra xung đột lịch rồi cập nhật trạng thái confirmed.
   - Nếu từ chối booking đã thanh toán, hệ thống hoàn tiền cho khách.
   - Owner có thể hủy booking đã xác nhận và hoàn tiền 100% cho khách nếu có thanh toán.
10. Với chức năng đổi lịch:
    - Owner xem yêu cầu đổi lịch từ User.
    - Nếu duyệt, hệ thống kiểm tra ca mới, cập nhật booking và hoàn/thu chênh lệch nếu có.
    - Nếu từ chối, hệ thống khôi phục ca cũ và hoàn tiền chênh lệch nếu khách đã trả.
11. Với chức năng ví/rút tiền:
    - Owner xem ví, cập nhật ngân hàng, nhập số tiền rút.
    - Hệ thống kiểm tra số dư và thông tin ngân hàng.
    - Nếu hợp lệ, hệ thống tạo yêu cầu rút tiền chờ Admin xử lý.
12. Hệ thống lưu thay đổi, ghi lịch sử và gửi thông báo.
13. Kết thúc luồng.

### 3.7.2.3. Mã Mermaid sơ đồ hoạt động Owner

```mermaid
flowchart TD
    A((Bắt đầu)) --> B[Owner truy cập cổng quản lý]
    B --> C{Đã đăng nhập?}
    C -- Không --> D[Đăng nhập Owner]
    D --> E{Tài khoản hợp lệ?}
    E -- Không --> F[Hiển thị lỗi]
    F --> D
    E -- Có --> G{Role owner và status active?}
    C -- Có --> G
    G -- Không --> H[Từ chối truy cập]
    G -- Có --> I[Hiển thị Dashboard Owner]

    I --> J{Chọn chức năng}
    J --> K[Quản lý cơ sở]
    J --> L[Quản lý sân con/ca sân]
    J --> M[Quản lý booking]
    J --> N[Xử lý đổi lịch]
    J --> O[Gói, dịch vụ, voucher]
    J --> P[Ví, rút tiền]
    J --> Q[Hợp đồng/chuyển nhượng]

    K --> K1[Nhập hoặc cập nhật thông tin cơ sở]
    K1 --> K2{Dữ liệu hợp lệ?}
    K2 -- Không --> F
    K2 -- Có --> K3{Thay đổi hồ sơ pháp lý?}
    K3 -- Có --> K4[Tạo yêu cầu chờ Admin duyệt]
    K3 -- Không --> K5[Cập nhật thông tin cơ bản]
    K4 --> Z((Kết thúc))
    K5 --> Z

    L --> L1[Chọn cơ sở thuộc sở hữu]
    L1 --> L2{Cơ sở active?}
    L2 -- Không --> F
    L2 -- Có --> L3[Thêm/sửa/xóa sân con hoặc ca sân]
    L3 --> L4{Có trùng lịch/booking tương lai?}
    L4 -- Có --> F
    L4 -- Không --> L5[Cập nhật sân, giá, ca hoặc khóa bảo trì]
    L5 --> Z

    M --> M1[Chọn booking]
    M1 --> M2{Booking đang chờ?}
    M2 -- Có --> M3{Xác nhận hay từ chối?}
    M3 -- Xác nhận --> M4[Kiểm tra xung đột và cập nhật confirmed]
    M3 -- Từ chối --> M5[Cập nhật rejected, hoàn tiền nếu đã thanh toán]
    M2 -- Không --> M6{Booking đã xác nhận và cần hủy?}
    M6 -- Có --> M7[Nhập lý do, hủy booking, hoàn tiền cho khách]
    M6 -- Không --> F
    M4 --> Z
    M5 --> Z
    M7 --> Z

    N --> N1[Xem yêu cầu đổi lịch]
    N1 --> N2{Duyệt yêu cầu?}
    N2 -- Có --> N3[Kiểm tra ca mới, cập nhật lịch]
    N2 -- Không --> N4[Khôi phục ca cũ, ghi lý do từ chối]
    N3 --> Z
    N4 --> Z

    O --> O1[Thêm/sửa/tắt/xóa gói, dịch vụ hoặc voucher]
    O1 --> O2{Dữ liệu hợp lệ và thuộc Owner?}
    O2 -- Không --> F
    O2 -- Có --> O3[Cập nhật dữ liệu]
    O3 --> Z

    P --> P1[Nhập thông tin rút tiền]
    P1 --> P2{Số dư và ngân hàng hợp lệ?}
    P2 -- Không --> F
    P2 -- Có --> P3[Tạo yêu cầu rút tiền chờ Admin]
    P3 --> Z

    Q --> Q1[Xem/ký/từ chối hợp đồng hoặc tạo chuyển nhượng]
    Q1 --> Q2{Hồ sơ/hợp đồng hợp lệ?}
    Q2 -- Không --> F
    Q2 -- Có --> Q3[Cập nhật trạng thái hợp đồng/chuyển nhượng]
    Q3 --> Z
```

## 3.7.3. Phân quyền Admin

### 3.7.3.1. Chức năng của Admin

Admin là quản trị viên hệ thống, có quyền quản lý toàn bộ nền tảng. Tài khoản Admin phải có vai trò `admin` và trạng thái `active`.

Các chức năng chính:

- Đăng nhập/đăng xuất khu vực Admin.
- Xem dashboard tổng quan hệ thống.
- Quản lý người dùng: xem danh sách, lọc theo vai trò, thêm tài khoản, xem chi tiết, cập nhật thông tin, vai trò và trạng thái.
- Quản lý cơ sở sân: xem danh sách, lọc/tìm kiếm, cập nhật trạng thái, duyệt/từ chối cơ sở, xem hồ sơ pháp lý, xóa cơ sở nếu không có booking tương lai.
- Duyệt hoặc từ chối yêu cầu cập nhật hồ sơ pháp lý của Owner.
- Quản lý sân con: xem danh sách, xem chi tiết, bật/tắt trạng thái, sửa, xóa, cập nhật hàng loạt.
- Quản lý booking: xem danh sách booking, hoàn tiền.
- Quản lý báo cáo vi phạm: xem báo cáo và cập nhật trạng thái xử lý.
- Quản lý gói đặt sân toàn hệ thống.
- Quản lý chatbot logs.
- Quản lý giao dịch và chi tiết giao dịch.
- Quản lý tài chính: xem doanh thu, hoa hồng, ví nền tảng, rút doanh thu.
- Quản lý yêu cầu rút tiền của Owner: duyệt, từ chối, tải minh chứng chuyển khoản.
- Cấu hình tài chính, cấu hình hệ thống.
- Quản lý hợp đồng: tạo, sửa, gửi, xuất PDF, chấm dứt hợp đồng.
- Quản lý chuyển nhượng cơ sở: xem, duyệt, từ chối, tạo hợp đồng mới cho chủ sân mới sau chuyển nhượng.
- Nhận và đọc thông báo hệ thống.

### 3.7.3.2. Các bước hoạt động tổng quát của Admin

1. Admin truy cập trang quản trị.
2. Hệ thống kiểm tra đăng nhập.
3. Nếu chưa đăng nhập, hệ thống chuyển đến trang đăng nhập Admin.
4. Admin nhập thông tin đăng nhập.
5. Hệ thống kiểm tra tài khoản có vai trò Admin và trạng thái active.
6. Nếu không hợp lệ, hệ thống từ chối truy cập.
7. Nếu hợp lệ, hệ thống hiển thị dashboard Admin.
8. Admin chọn chức năng quản trị cần xử lý.
9. Với quản lý người dùng:
   - Admin xem, tìm kiếm, lọc danh sách người dùng.
   - Admin thêm mới hoặc cập nhật tài khoản.
   - Hệ thống kiểm tra email, mật khẩu, vai trò và trạng thái.
   - Nếu hợp lệ, hệ thống lưu dữ liệu; nếu không, hiển thị lỗi.
10. Với quản lý cơ sở:
    - Admin xem danh sách cơ sở đang chờ duyệt hoặc đang hoạt động.
    - Admin xem hồ sơ pháp lý.
    - Nếu hồ sơ hợp lệ, Admin duyệt cơ sở và hệ thống chuyển trạng thái sang approved.
    - Nếu hồ sơ không hợp lệ, Admin nhập lý do từ chối và hệ thống chuyển trạng thái sang rejected.
    - Với cơ sở đã có lịch đặt tương lai, hệ thống không cho xóa hoặc chấm dứt ảnh hưởng đến khách hàng.
11. Với quản lý hợp đồng:
    - Admin tạo hợp đồng cho Owner và cơ sở đã duyệt.
    - Admin gửi hợp đồng cho Owner ký.
    - Nếu Owner ký và hợp đồng còn hiệu lực, hệ thống kích hoạt cơ sở sang active.
    - Admin có thể xuất PDF hoặc chấm dứt hợp đồng nếu đủ điều kiện.
12. Với quản lý booking và báo cáo:
    - Admin xem danh sách booking hoặc báo cáo vi phạm.
    - Admin xử lý hoàn tiền hoặc cập nhật trạng thái báo cáo.
13. Với quản lý tài chính:
    - Admin xem giao dịch, doanh thu, công nợ và yêu cầu rút tiền.
    - Nếu duyệt rút tiền, Admin tải minh chứng, hệ thống ghi nhận giao dịch trừ ví Owner và gửi thông báo.
    - Nếu từ chối, Admin nhập ghi chú và hệ thống cập nhật trạng thái rejected.
14. Với chuyển nhượng cơ sở:
    - Admin xem hợp đồng chuyển nhượng đã được bên nhận ký.
    - Nếu duyệt, hệ thống chuyển owner của cơ sở, chấm dứt hợp đồng cũ và đưa cơ sở về trạng thái approved chờ hợp đồng mới.
    - Nếu từ chối, hệ thống ghi lý do và thông báo cho Owner.
15. Hệ thống lưu thay đổi, cập nhật trạng thái và gửi thông báo.
16. Kết thúc luồng.

### 3.7.3.3. Mã Mermaid sơ đồ hoạt động Admin

```mermaid
flowchart TD
    A((Bắt đầu)) --> B[Admin truy cập trang quản trị]
    B --> C{Đã đăng nhập?}
    C -- Không --> D[Nhập tài khoản Admin]
    D --> E{Thông tin hợp lệ?}
    E -- Không --> F[Hiển thị lỗi]
    F --> D
    E -- Có --> G{Role admin và status active?}
    C -- Có --> G
    G -- Không --> H[Từ chối truy cập]
    G -- Có --> I[Hiển thị Dashboard Admin]

    I --> J{Chọn chức năng}
    J --> K[Quản lý người dùng]
    J --> L[Quản lý cơ sở]
    J --> M[Quản lý sân/booking/báo cáo]
    J --> N[Quản lý tài chính]
    J --> O[Quản lý hợp đồng]
    J --> P[Quản lý chuyển nhượng]
    J --> Q[Cài đặt hệ thống/chatbot/gói]

    K --> K1[Thêm, xem, lọc hoặc cập nhật người dùng]
    K1 --> K2{Dữ liệu hợp lệ?}
    K2 -- Không --> F
    K2 -- Có --> K3[Lưu role, trạng thái, thông tin tài khoản]
    K3 --> Z((Kết thúc))

    L --> L1[Xem cơ sở và hồ sơ pháp lý]
    L1 --> L2{Chọn thao tác}
    L2 --> L3[Duyệt cơ sở]
    L2 --> L4[Từ chối cơ sở]
    L2 --> L5[Cập nhật/xóa cơ sở]
    L3 --> L6[Cập nhật trạng thái approved, gửi thông báo Owner]
    L4 --> L7[Nhập lý do, cập nhật rejected, gửi thông báo]
    L5 --> L8{Có booking tương lai?}
    L8 -- Có --> F
    L8 -- Không --> L9[Cập nhật hoặc xóa cơ sở]
    L6 --> Z
    L7 --> Z
    L9 --> Z

    M --> M1[Xem sân, booking hoặc báo cáo]
    M1 --> M2{Chọn thao tác xử lý?}
    M2 --> M3[Bật/tắt/sửa/xóa sân]
    M2 --> M4[Hoàn tiền booking]
    M2 --> M5[Cập nhật trạng thái báo cáo]
    M3 --> Z
    M4 --> Z
    M5 --> Z

    N --> N1[Xem giao dịch, doanh thu, yêu cầu rút tiền]
    N1 --> N2{Duyệt rút tiền?}
    N2 -- Có --> N3[Tải minh chứng, trừ ví Owner, gửi thông báo]
    N2 -- Không --> N4[Nhập ghi chú, cập nhật rejected]
    N3 --> Z
    N4 --> Z

    O --> O1[Tạo/sửa/gửi hợp đồng]
    O1 --> O2{Owner ký hợp đồng?}
    O2 -- Chưa --> O3[Chờ Owner xử lý]
    O2 -- Đã ký --> O4[Kích hoạt cơ sở nếu hợp đồng còn hiệu lực]
    O3 --> Z
    O4 --> Z

    P --> P1[Xem yêu cầu chuyển nhượng đã ký]
    P1 --> P2{Duyệt chuyển nhượng?}
    P2 -- Có --> P3[Chuyển owner, chấm dứt hợp đồng cũ, chờ hợp đồng mới]
    P2 -- Không --> P4[Nhập lý do từ chối]
    P3 --> Z
    P4 --> Z

    Q --> Q1[Cấu hình hệ thống, tài chính, chatbot, gói]
    Q1 --> Z
```

## Gợi ý trình bày trong báo cáo

- Với mỗi sơ đồ, nút bắt đầu và kết thúc dùng hình tròn.
- Các bước xử lý như "Đăng nhập", "Chọn chức năng", "Cập nhật dữ liệu" dùng hình chữ nhật.
- Các bước kiểm tra như "Dữ liệu hợp lệ?", "Role đúng?", "Có booking tương lai?" dùng hình thoi.
- Nhánh đúng ghi "True/Có", nhánh sai ghi "False/Không" giống mẫu trong ảnh.
- Sau bước thành công hoặc lỗi, mũi tên đưa về kết thúc hoặc quay lại form nhập liệu tùy nghiệp vụ.
