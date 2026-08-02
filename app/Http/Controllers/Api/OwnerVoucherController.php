<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreVoucherRequest;
use App\Services\VoucherService;
use Exception;
use Illuminate\Http\JsonResponse;

class OwnerVoucherController extends Controller
{
    protected VoucherService $voucherService;

    public function __construct(VoucherService $voucherService)
    {
        $this->voucherService = $voucherService;
    }

    /**
     * Store a newly created voucher in storage.
     */
    public function store(StoreVoucherRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            
            // Get current owner id (assuming owner is logged in via sanctum)
            $data['owner_id'] = $request->user()->id;

            $voucher = $this->voucherService->create($data);

            return response()->json([
                'success' => true,
                'message' => 'Voucher created successfully.',
                'data' => $voucher->load('venues'),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create voucher.',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
