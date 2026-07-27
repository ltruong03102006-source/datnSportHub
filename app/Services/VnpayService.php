<?php

namespace App\Services;

use App\Models\TopupTransaction;
use RuntimeException;

class VnpayService
{
    public function verifyReturn(array $input): bool
    {
        $secureHash = $input['vnp_SecureHash'] ?? null;
        $hashSecret = config('services.vnpay.hash_secret');

        if (! $secureHash || ! $hashSecret) {
            return false;
        }

        $params = [];
        foreach ($input as $key => $value) {
            if (str_starts_with((string) $key, 'vnp_')) {
                $params[$key] = $value;
            }
        }

        unset($params['vnp_SecureHash'], $params['vnp_SecureHashType']);

        $hashData = $this->buildHashData($params);
        $calculatedHash = hash_hmac('sha512', $hashData, $hashSecret);

        return hash_equals($calculatedHash, $secureHash);
    }

    public function createTopupUrl(TopupTransaction $topup): string
    {
        $vnpUrl = config('services.vnpay.url');
        $tmnCode = config('services.vnpay.tmn_code');
        $hashSecret = config('services.vnpay.hash_secret');
        $returnUrl = config('services.vnpay.topup_return_url') ?: route('owner.wallet.topup.callback');

        if (! $vnpUrl || ! $tmnCode || ! $hashSecret) {
            throw new RuntimeException('Thiếu cấu hình VNPay. Vui lòng kiểm tra VNPAY_TMN_CODE, VNPAY_HASH_SECRET, VNPAY_URL.');
        }

        $txnRef = $topup->vnpay_txn_ref ?: $topup->code;

        $params = [
            'vnp_Version' => '2.1.0',
            'vnp_TmnCode' => $tmnCode,
            'vnp_Amount' => (int) round((float) $topup->amount * 100),
            'vnp_Command' => 'pay',
            'vnp_CreateDate' => now()->format('YmdHis'),
            'vnp_CurrCode' => 'VND',
            'vnp_IpAddr' => request()->ip() ?: '127.0.0.1',
            'vnp_Locale' => 'vn',
            'vnp_OrderInfo' => 'Nap tien vi SportHub ' . $topup->code,
            'vnp_OrderType' => 'other',
            'vnp_ReturnUrl' => $returnUrl,
            'vnp_TxnRef' => $txnRef,
        ];

        $hashData = $this->buildHashData($params);
        $query = $this->buildQuery($params);

        $secureHash = hash_hmac('sha512', $hashData, $hashSecret);

        return $vnpUrl . '?' . $query . 'vnp_SecureHash=' . $secureHash;
    }

    private function buildHashData(array $params): string
    {
        ksort($params);

        $hashData = '';
        $index = 0;

        foreach ($params as $key => $value) {
            if ($index === 1) {
                $hashData .= '&' . urlencode($key) . '=' . urlencode($value);
            } else {
                $hashData .= urlencode($key) . '=' . urlencode($value);
                $index = 1;
            }
        }

        return $hashData;
    }

    private function buildQuery(array $params): string
    {
        ksort($params);

        $query = '';

        foreach ($params as $key => $value) {
            $query .= urlencode($key) . '=' . urlencode($value) . '&';
        }

        return $query;
    }
}
