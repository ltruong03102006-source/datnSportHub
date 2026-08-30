<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Yêu cầu rút tiền mới</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333333; background-color: #f4f6f9; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <div style="background-color: #1e40af; color: #ffffff; padding: 20px; text-align: center;">
            <h2 style="margin: 0; font-size: 20px;">SportHub - Thông Báo Rút Tiền</h2>
        </div>
        
        <div style="padding: 24px;">
            <p>Xin chào Admin,</p>
            
            <p>Hệ thống vừa nhận được một <strong>yêu cầu rút tiền mới</strong> cần được kiểm tra và phê duyệt.</p>
            
            <div style="background-color: #f8fafc; border-left: 4px solid #1e40af; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <h3 style="margin-top: 0; margin-bottom: 12px; color: #1e40af; font-size: 16px;">Thông tin yêu cầu rút tiền</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 6px 0; color: #64748b; width: 40%;">Mã yêu cầu:</td>
                        <td style="padding: 6px 0; font-weight: bold; color: #0f172a;">#{{ $withdrawal->code }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #64748b;">{{ $roleText ?? 'Người yêu cầu' }}:</td>
                        <td style="padding: 6px 0; font-weight: bold; color: #0f172a;">{{ $requester->name ?? 'N/A' }} ({{ $requester->email ?? 'N/A' }})</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #64748b;">Số tiền yêu cầu:</td>
                        <td style="padding: 6px 0; font-weight: bold; color: #dc2626; font-size: 18px;">{{ number_format((float)$withdrawal->amount, 0, ',', '.') }}đ</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #64748b;">Ngân hàng nhận:</td>
                        <td style="padding: 6px 0; font-weight: bold; color: #0f172a;">{{ $withdrawal->bank_name }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #64748b;">Số tài khoản:</td>
                        <td style="padding: 6px 0; font-weight: bold; color: #0f172a;">{{ $withdrawal->bank_account_number }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #64748b;">Chủ tài khoản:</td>
                        <td style="padding: 6px 0; font-weight: bold; color: #0f172a;">{{ $withdrawal->bank_account_holder }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #64748b;">Thời gian tạo:</td>
                        <td style="padding: 6px 0; color: #0f172a;">{{ $withdrawal->created_at ? $withdrawal->created_at->format('d/m/Y H:i:s') : now()->format('d/m/Y H:i:s') }}</td>
                    </tr>
                </table>
            </div>

            <p style="text-align: center; margin-top: 30px;">
                <a href="{{ $adminUrl }}" style="display: inline-block; padding: 12px 24px; background-color: #1e40af; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold;">Xem và Duyệt Yêu Cầu Rút Tiền</a>
            </p>

            <p style="margin-top: 30px; font-size: 13px; color: #64748b; border-top: 1px solid #e2e8f0; padding-top: 15px;">
                Đây là email tự động từ hệ thống SportHub. Vui lòng không trả lời email này.
            </p>
        </div>
    </div>
</body>
</html>
