<?php

namespace App\Enums;

enum TransactionType: string
{
    case DEPOSIT = 'deposit';
    case PAYMENT = 'payment';
    case BOOKING_INCOME = 'booking_income';
    case BOOKING_ONLINE_CREDIT = 'booking_online_credit';
    case COMMISSION_FEE = 'commission_fee';
    case COMMISSION_COD_DEBIT = 'commission_cod_debit';
    case TOPUP = 'topup';
    case TOPUP_CREDIT = 'topup_credit';
    case WITHDRAW = 'withdraw';
    case WITHDRAWAL_DEBIT = 'withdrawal_debit';
    case REFUND = 'refund';
    case ADJUSTMENT = 'adjustment';
}
