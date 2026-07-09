<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Hóa đơn đặt sân - #{{ $booking->id }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 14px;
            color: #333;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #059669;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #059669;
            margin: 0;
            font-size: 28px;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0 0 0;
            color: #666;
        }
        .row {
            width: 100%;
            display: table;
            margin-bottom: 20px;
        }
        .col-half {
            width: 48%;
            display: table-cell;
            vertical-align: top;
        }
        .info-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 15px;
            border-radius: 8px;
        }
        .info-box h3 {
            margin-top: 0;
            margin-bottom: 10px;
            color: #0f172a;
            font-size: 16px;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 5px;
        }
        .info-row {
            margin-bottom: 8px;
        }
        .info-label {
            font-weight: bold;
            color: #475569;
            width: 130px;
            display: inline-block;
        }
        table.invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            margin-bottom: 30px;
        }
        table.invoice-table th, table.invoice-table td {
            border: 1px solid #cbd5e1;
            padding: 12px;
            text-align: left;
        }
        table.invoice-table th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 13px;
        }
        .text-right {
            text-align: right !important;
        }
        .text-center {
            text-align: center !important;
        }
        .total-row td {
            font-weight: bold;
            font-size: 16px;
            background-color: #ecfdf5;
            color: #065f46;
        }
        .footer {
            text-align: center;
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px dashed #cbd5e1;
            color: #64748b;
            font-size: 12px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            background-color: #ecfdf5;
            color: #059669;
            border: 1px solid #059669;
            border-radius: 4px;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>SportHub</h1>
            <p>Nền tảng đặt sân thể thao số 1 Việt Nam</p>
            <p>HÓA ĐƠN ĐIỆN TỬ - MÃ: <strong>#{{ str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}</strong></p>
        </div>

        <div class="row">
            <div class="col-half" style="padding-right: 2%;">
                <div class="info-box">
                    <h3>THÔNG TIN KHÁCH HÀNG</h3>
                    <div class="info-row"><span class="info-label">Họ tên:</span> {{ $booking->user->name }}</div>
                    <div class="info-row"><span class="info-label">SĐT:</span> {{ $booking->user->phone ?? 'Không cung cấp' }}</div>
                    <div class="info-row"><span class="info-label">Email:</span> {{ $booking->user->email }}</div>
                    <div class="info-row"><span class="info-label">Ngày xuất HĐ:</span> {{ date('d/m/Y H:i') }}</div>
                </div>
            </div>
            <div class="col-half" style="padding-left: 2%;">
                <div class="info-box">
                    <h3>THÔNG TIN CƠ SỞ</h3>
                    <div class="info-row"><span class="info-label">Điểm sân:</span> {{ $booking->court->venue->name }}</div>
                    <div class="info-row"><span class="info-label">Sân con:</span> {{ $booking->court->name }}</div>
                    <div class="info-row"><span class="info-label">Địa chỉ:</span> {{ $booking->court->venue->address }}</div>
                    <div class="info-row"><span class="info-label">Trạng thái TT:</span> <span class="status-badge">Đã thanh toán</span></div>
                </div>
            </div>
        </div>

        <table class="invoice-table">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Nội dung / Giờ chơi</th>
                    <th class="text-center">Ngày chơi</th>
                    <th class="text-right">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-center">1</td>
                    <td>
                        Thuê sân: {{ $booking->court->venue->name }} - {{ $booking->court->name }}<br>
                        Thời gian: <strong>{{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}</strong>
                    </td>
                    <td class="text-center">
                        {{ \Carbon\Carbon::parse($booking->slot_date)->format('d/m/Y') }}
                    </td>
                    <td class="text-right">
                        {{ number_format($booking->total_price, 0, ',', '.') }}đ
                    </td>
                </tr>
                <tr class="total-row">
                    <td colspan="3" class="text-right">TỔNG CỘNG:</td>
                    <td class="text-right">{{ number_format($booking->total_price, 0, ',', '.') }}đ</td>
                </tr>
                @if($booking->status === 'cancelled')
                <tr>
                    <td colspan="4" style="padding: 15px; background-color: #fff1f2; border: 1px solid #fda4af;">
                        <h4 style="margin: 0 0 10px 0; color: #be123c; font-size: 14px; text-transform: uppercase;">THÔNG TIN HỦY & HOÀN TIỀN</h4>
                        <table style="width: 100%; border: none;">
                            <tr>
                                <td style="border: none; padding: 4px 0; width: 130px; font-weight: bold; color: #4c0519;">Lý do hủy:</td>
                                <td style="border: none; padding: 4px 0; color: #9f1239;">{{ $booking->cancel_reason ?? 'Không có lý do' }}</td>
                            </tr>
                            <tr>
                                <td style="border: none; padding: 4px 0; font-weight: bold; color: #4c0519;">Phí phạt hủy:</td>
                                <td style="border: none; padding: 4px 0; color: #333;">{{ number_format($booking->cancellation_fee ?? 0, 0, ',', '.') }}đ</td>
                            </tr>
                            <tr>
                                <td style="border: none; padding: 4px 0; font-weight: bold; color: #4c0519;">Số tiền hoàn lại:</td>
                                <td style="border: none; padding: 4px 0; color: #059669; font-weight: bold; font-size: 16px;">{{ number_format($booking->refund_amount ?? 0, 0, ',', '.') }}đ</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                @endif
            </tbody>
        </table>

        <div class="footer">
            <p>Cảm ơn quý khách đã sử dụng dịch vụ tại SportHub!</p>
            <p>Mọi thắc mắc xin vui lòng liên hệ bộ phận hỗ trợ khách hàng qua email: support@sporthub.vn</p>
        </div>
    </div>
</body>
</html>
