<div class="contract-document">
    <div class="center text-center mb-3" style="text-align: center;">
        <p class="bold" style="text-align: center; font-weight: bold; margin: 0 0 4px 0;">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</p>
        <p class="bold" style="text-align: center; font-weight: bold; margin: 0 0 4px 0;">Độc lập - Tự do - Hạnh phúc</p>
        <p style="text-align: center; margin: 0 0 12px 0;">----------------------</p>
    </div>

    <div class="center text-center mb-3" style="text-align: center;">
        <h1 style="text-align: center; font-size: 20px; font-weight: bold; margin: 0 0 8px 0;">{{ strtoupper($contract->title ?? 'HỢP ĐỒNG HỢP TÁC KINH DOANH') }}</h1>
        <p style="text-align: center; margin: 0 0 4px 0;">Số: {{ $contract->contract_code ?? '...' }}</p>
        <p style="text-align: center; margin: 0 0 12px 0;">Ngày lập: {{ $contract->created_at ? $contract->created_at->format('d/m/Y') : now()->format('d/m/Y') }}</p>
    </div>

    <div class="section">
        <p><strong>Căn cứ pháp lý:</strong></p>
        <p>- Căn cứ Bộ luật Dân sự năm 2015;</p>
        <p>- Căn cứ Luật Thương mại năm 2005;</p>
        <p>- Căn cứ nhu cầu và khả năng hợp tác của hai bên.</p>
    </div>

    <div class="section">
        <p><strong>Thông tin các bên</strong></p>
        <p><strong>Bên A (Nền tảng SportHub)</strong></p>
        <p>Tên đơn vị: SportHub</p>
        <p>Vai trò: Đơn vị cung cấp nền tảng quản lý và đặt sân trực tuyến</p>
        <p>Người đại diện: Quản trị viên SportHub</p>

        <p class="mb-2"></p>
        <p><strong>Bên B (Chủ cơ sở thể thao)</strong></p>
        <p>Họ tên: {{ $owner?->name ?? '...' }}</p>
        <p>Tên cơ sở: {{ $venue?->name ?? 'Chưa gắn cơ sở cụ thể' }}</p>
        <p>Địa chỉ cơ sở: {{ $venue?->address ?? '...' }}</p>
        <p>CCCD: {{ $venue?->legalDocument?->citizen_id ?? $owner?->citizen_id ?? '...' }}</p>
        <p>Số điện thoại: {{ $venue?->phone ?? $owner?->phone ?? 'Chưa cập nhật' }}</p>
        <p>Email: {{ $owner?->email ?? '...' }}</p>
        <p>Ngân hàng: {{ $venue?->legalDocument?->bank_name ?? $owner?->bank_name ?? '...' }}</p>
        <p>Số tài khoản: {{ $venue?->legalDocument?->bank_account_number ?? $owner?->bank_account_no ?? '...' }}</p>
        <p>Chủ tài khoản: {{ $venue?->legalDocument?->bank_account_holder ?? $owner?->bank_account_name ?? '...' }}</p>
    </div>

    <div class="section">
        <p><strong>Điều 1. Nội dung hợp tác</strong></p>
        <p>SportHub cung cấp nền tảng đặt sân trực tuyến, công cụ quản lý lịch, xử lý đặt sân và hỗ trợ thanh toán.</p>
        <p>Bên B cung cấp cơ sở thể thao, thông tin sân, khung giờ, giá dịch vụ và chịu trách nhiệm vận hành thực tế tại cơ sở.</p>
        <p>Các giao dịch phát sinh trên nền tảng được ghi nhận, đối soát và phân chia doanh thu theo chính sách tại hợp đồng này.</p>
    </div>

    <div class="section">
        <p><strong>Điều 2. Quyền và nghĩa vụ của Bên A</strong></p>
        <p>SportHub có quyền:</p>
        <p>- Thu tiền từ khách hàng theo giao dịch phát sinh trên nền tảng.</p>
        <p>- Thu hoa hồng theo tỷ lệ đã thỏa thuận.</p>
        <p>- Kiểm tra chất lượng thông tin cơ sở, lịch sân và dịch vụ đi kèm.</p>
        <p>- Tạm khóa hoặc chấm dứt hiển thị cơ sở khi Bên B vi phạm nghiêm trọng quy định nền tảng.</p>
        <p>Nghĩa vụ:</p>
        <p>- Ghi nhận doanh thu, hoa hồng và khoản phải trả một cách minh bạch.</p>
        <p>- Bảo mật dữ liệu người dùng và dữ liệu giao dịch theo quy định pháp luật.</p>
        <p>- Hỗ trợ vận hành, khiếu nại và các vấn đề phát sinh trong phạm vi nền tảng.</p>
    </div>

    <div class="section">
        <p><strong>Điều 3. Quyền và nghĩa vụ của Bên B</strong></p>
        <p>Bên B có quyền:</p>
        <p>- Nhận doanh thu sau khi trừ hoa hồng và các khoản phát sinh hợp lệ.</p>
        <p>- Gửi yêu cầu rút tiền, đối soát và khiếu nại giao dịch.</p>
        <p>- Đề xuất cập nhật thông tin cơ sở, chính sách giá và dịch vụ.</p>
        <p>Nghĩa vụ:</p>
        <p>- Cập nhật lịch sân chính xác, phục vụ khách đúng khung giờ đã xác nhận.</p>
        <p>- Cung cấp hồ sơ pháp lý trung thực và còn hiệu lực.</p>
        <p>- Không tự ý hủy sân hoặc thu thêm phí ngoài thông tin đã công bố nếu chưa có thỏa thuận rõ ràng.</p>
    </div>

    <div class="section">
        <p><strong>Điều 4. Giá trị hợp đồng và đối soát</strong></p>
        <p>Hoa hồng nền tảng: {{ number_format((float) ($contract->commission_rate ?? 0), 2) }}% trên doanh thu phát sinh từ các giao dịch hợp lệ.</p>
        <p>Chu kỳ đối soát: Hàng tuần hoặc theo cấu hình tài chính đang áp dụng trên hệ thống.</p>
        <p>Thời gian thanh toán: Trong vòng 03 ngày làm việc kể từ khi yêu cầu rút tiền hợp lệ được duyệt.</p>
        <p>Ngưỡng rút tối thiểu: Theo cấu hình tài chính đang áp dụng trên hệ thống.</p>
    </div>

    <div class="section">
        <p><strong>Điều 5. Hiệu lực và kích hoạt cơ sở</strong></p>
        <p>Hợp đồng có hiệu lực từ ngày {{ $contract->start_date?->format('d/m/Y') ?? '...' }} đến hết ngày {{ $contract->end_date?->format('d/m/Y') ?? '...' }}.</p>
        <p>Sau khi Bên B xác nhận hợp đồng và đến ngày bắt đầu hiệu lực, hệ thống sẽ kích hoạt cơ sở liên quan và áp dụng tỷ lệ hoa hồng nêu tại hợp đồng.</p>
    </div>

    <div class="section">
        <p><strong>Điều 6. Xử lý vi phạm</strong></p>
        <p>Bên B vi phạm khi hủy sân nhiều lần không có lý do chính đáng, đăng thông tin sai lệch, gian lận giao dịch hoặc không đảm bảo chất lượng phục vụ.</p>
        <p>SportHub có quyền cảnh cáo, tạm khóa cơ sở, giữ giao dịch để đối soát hoặc chấm dứt hợp đồng theo mức độ vi phạm.</p>
    </div>

    <div class="section">
        <p><strong>Điều 7. Chấm dứt hợp đồng</strong></p>
        <p>Hợp đồng chấm dứt khi hết thời hạn, hai bên có thỏa thuận bằng văn bản hoặc một bên vi phạm nghiêm trọng nghĩa vụ đã cam kết.</p>
    </div>

    <div class="section">
        <p><strong>Điều khoản chung</strong></p>
        <p>Hai bên cam kết thực hiện đúng nội dung hợp đồng. Tranh chấp phát sinh sẽ được ưu tiên giải quyết bằng thương lượng trước khi chuyển đến cơ quan có thẩm quyền.</p>
    </div>
    <table style="width: 100%; margin-top: 40px; text-align: center;">
    <tr>
        <td style="width: 50%; vertical-align: top;">
            <p class="bold">ĐẠI DIỆN BÊN A</p>
            <p><em>(Đã ban hành)</em></p>
            <p><strong>SPORTHUB SYSTEM</strong></p>
        </td>
        <td style="width: 50%; vertical-align: top;">
            <p class="bold">ĐẠI DIỆN BÊN B</p>
            @if($contract->status === 'accepted' || $contract->status === 'expired' || $contract->status === 'terminated')
                <p><em>(Đã xác nhận ký điện tử)</em></p>
                <p style="font-size: 11px;">Thời gian: {{ $contract->signed_at?->format('H:i:s d/m/Y') }}</p>
                <p style="font-size: 11px;">Tài khoản: {{ $owner?->email }}</p>
                <p style="font-size: 11px;">IP xác thực: {{ $contract->signed_ip }}</p>
            @else
                <p><em>(Chờ chủ sân xác nhận)</em></p>
            @endif
        </td>
    </tr>
</table>
</div>
