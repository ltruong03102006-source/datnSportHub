<?php

namespace App\Enums;

enum TransactionType: string
{
    case BOOKING_INCOME = 'booking_income';
    case COMMISSION_FEE = 'commission_fee';
    case TOPUP = 'topup';
    case WITHDRAW = 'withdraw';
    case REFUND = 'refund';
    case ADJUSTMENT = 'adjustment';
}