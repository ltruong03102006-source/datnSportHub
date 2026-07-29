<?php

namespace App\Enums;

enum SettlementStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case SETTLED = 'settled';
    case FAILED = 'failed';
}