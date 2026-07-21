<?php

namespace App\Enums;

enum SettlementStatus: string
{
    case PENDING = 'pending';       // Chờ xử lý đối soát
    case PROCESSING = 'processing'; // Đang trong quá trình tính toán/chuyển tiền
    case SETTLED = 'settled';       // Đã đối soát và chia tiền vào ví xong
    case FAILED = 'failed';         // Lỗi trong quá trình đối soát
}