<?php

namespace App\Enums;

enum TransactionType: string
{
    case DEPOSIT = 'deposit';
    case PAYMENT = 'payment';
    case BOOKING_INCOME = 'booking_income';
    case COMMISSION_FEE = 'commission_fee';
    case TOPUP = 'topup';
    case TOPUP_CREDIT = 'topup_credit';
    case WITHDRAW = 'withdraw';
    case REFUND = 'refund';
    case ADJUSTMENT = 'adjustment';
}
