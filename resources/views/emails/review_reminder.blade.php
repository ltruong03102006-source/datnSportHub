<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Nhac danh gia tran dau</title>
</head>
<body style="margin:0; padding:24px; background:#f6f8fb; font-family:Arial, sans-serif; color:#172033;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:600px; margin:0 auto; background:#ffffff; border:1px solid #e5e7eb; border-radius:8px;">
        <tr>
            <td style="padding:32px;">
                <h1 style="margin:0 0 16px; font-size:24px;">Cam on ban da dat san</h1>
                <p style="margin:0 0 14px; line-height:1.6;">Chao {{ $user->name }}, tran dau cua ban tai <strong>{{ $venue?->name ?? 'SportHub' }}</strong> da hoan thanh.</p>
                <p style="margin:0 0 24px; line-height:1.6;">Hay danh gia san <strong>{{ $court?->name ?? 'da dat' }}</strong> de giup cong dong co them thong tin huu ich.</p>
                <a href="{{ route('account.bookings.index') }}" style="display:inline-block; padding:12px 18px; border-radius:6px; background:#059669; color:#ffffff; text-decoration:none; font-weight:bold;">Viet danh gia</a>
                <p style="margin:24px 0 0; color:#64748b; font-size:13px;">Ma booking: #{{ $booking->id }}</p>
            </td>
        </tr>
    </table>
</body>
</html>
