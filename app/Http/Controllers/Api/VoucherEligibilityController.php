<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\VoucherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VoucherEligibilityController extends Controller
{
    protected VoucherService $voucherService;

    public function __construct(VoucherService $voucherService)
    {
        $this->voucherService = $voucherService;
    }

    /**
     * Get list of vouchers available for a court, decorated with eligibility flags.
     */
    public function getAvailableVouchers(Request $request, int $courtId): JsonResponse
    {
        $date = $request->input('date', today()->toDateString());
        $totalPrice = (float) $request->input('total_price', 0);
        
        // Slots should be passed as an array of ['start_time' => 'HH:MM', 'end_time' => 'HH:MM']
        $slots = $request->input('slots', []);
        if (is_string($slots)) {
            $slots = json_decode($slots, true) ?? [];
        }

        // Try to get authenticated user ID from either Sanctum/session or token query param
        $userId = $request->user()?->id ?? Auth::id();

        // If frontend sends token in query param (for SPA storing token in localStorage), try to resolve user via Sanctum PersonalAccessToken
        if (empty($userId) && $request->query('token')) {
            try {
                $token = $request->query('token');
                $pat = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
                if ($pat && $pat->tokenable) {
                    $userId = $pat->tokenable->id;
                }
            } catch (\Throwable $e) {
                // ignore token resolution errors and treat as guest
            }
        }

        try {
            $vouchers = $this->voucherService->getAvailableVouchersForCourt(
                $courtId,
                $date,
                $slots,
                $totalPrice,
                $userId
            );

            return response()->json([
                'success' => true,
                'data' => $vouchers,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi kiểm tra danh sách voucher.',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
