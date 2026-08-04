<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'venue_id',
        'created_by',
        'contract_code',
        'title',
        'content',
        'commission_rate',
        'start_date',
        'end_date',
        'status',
        'pdf_path',
        'signed_at',
        'sent_at',
        'rejected_at',
        'expired_at',
        'terminated_at',
        'rejection_reason',
        'note',
        'signed_ip',
    'signed_user_agent',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'signed_at' => 'datetime',
        'sent_at' => 'datetime',
        'rejected_at' => 'datetime',
        'expired_at' => 'datetime',
        'terminated_at' => 'datetime',
    ];

    /**
     * The owner of the contract.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * The admin user who created the contract.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The venue covered by the contract.
     */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public static function buildStandardContent(string $ownerName = '[Tên chủ sân]', ?string $venueName = null, ?string $commissionRate = null, ?string $startDate = null, ?string $endDate = null): string
    {
        $venueName = $venueName ?: '[Tên cơ sở]';
        $commissionRate = $commissionRate !== null ? number_format((float) $commissionRate, 2) : '[x]';
        $startDate = $startDate ?: '[ngày/tháng/năm]';
        $endDate = $endDate ?: '[ngày/tháng/năm]';

        return <<<TEXT
CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM
Độc lập - Tự do - Hạnh phúc
-----o0o-----

HỢP ĐỒNG HỢP TÁC KINH DOANH

Số: [Mã hợp đồng]
Ngày: [Ngày lập]

Căn cứ Bộ luật Dân sự và các quy định pháp luật liên quan;
Căn cứ nhu cầu hợp tác giữa các bên nhằm phát triển hoạt động cung cấp dịch vụ thể thao và quản lý cơ sở vận hành trên nền tảng SportHub;

I. THÔNG TIN CÁC BÊN
1. Bên A (Bên cung cấp nền tảng): SportHub.
2. Bên B (Bên sử dụng dịch vụ): {$ownerName}, đại diện cho cơ sở {$venueName}.

II. NỘI DUNG HỢP ĐỒNG
1. Mục đích của hợp đồng là tạo cơ sở pháp lý để Bên B đưa cơ sở thể thao vào hoạt động trên nền tảng SportHub và thực hiện các giao dịch đặt sân, dịch vụ và các hoạt động liên quan.
2. Bên B cam kết cung cấp thông tin trung thực, đầy đủ và chịu trách nhiệm trước pháp luật về các nội dung liên quan đến cơ sở, quy định nội quy, điều kiện vận hành và các tài liệu pháp lý có liên quan.
3. Bên A có quyền hỗ trợ tiếp cận khách hàng, quảng bá dịch vụ, quản lý đặt lịch và thực hiện thanh toán theo quy định của hệ thống.
4. Tỷ lệ hoa hồng áp dụng theo thỏa thuận là {$commissionRate}% trên doanh thu phát sinh từ các giao dịch được xác nhận bởi hệ thống.
5. Thời hạn hiệu lực của hợp đồng từ ngày {$startDate} đến hết ngày {$endDate}.

III. TRÁCH NHIỆM CỦA CÁC BÊN
1. Bên B có trách nhiệm cập nhật thông tin cơ sở, đảm bảo tính hợp pháp của hoạt động kinh doanh và tuân thủ các quy định hiện hành.
2. Bên A có trách nhiệm cung cấp nền tảng, bảo mật dữ liệu, hỗ trợ vận hành và thực hiện các giao dịch theo đúng quy định.
3. Trong trường hợp có tranh chấp, các bên ưu tiên thương lượng, hòa giải trước khi khởi kiện ra Tòa án có thẩm quyền.

IV. CHẤM DỨT VÀ GIẢI QUYẾT TRANH CHẤP
1. Hợp đồng có thể chấm dứt khi một bên vi phạm nghiêm trọng điều khoản này, hoặc khi có thỏa thuận chung bằng văn bản.
2. Tranh chấp phát sinh sẽ được giải quyết trên nguyên tắc thiện chí, trung thực và đúng pháp luật Việt Nam.

V. HIỆU LỰC
Hợp đồng có hiệu lực kể từ thời điểm Bên B ký xác nhận và được Bên A tiếp nhận trên hệ thống. Các bên cam kết thực hiện đúng các điều khoản trên.
TEXT;
    }

    public static function renderTemplate(self $contract, ?User $owner = null, ?Venue $venue = null): string
    {
        $owner = $owner ?? $contract->owner;
        $venue = $venue ?? $contract->venue;

        return view('admin.contracts.partials.body', compact('contract', 'owner', 'venue'))->render();
    }
}
