<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Services\VoucherService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AdminVoucherController extends Controller
{
    protected VoucherService $voucherService;

    public function __construct(VoucherService $voucherService)
    {
        $this->voucherService = $voucherService;
    }

    /**
     * Display a listing of system vouchers.
     */
    public function index(Request $request)
    {
        $query = Voucher::whereNull('owner_id'); // System Vouchers only

        // Filter by search keyword (code or name)
        if ($request->filled('keyword')) {
            $keyword = trim($request->input('keyword'));
            $query->where(function ($q) use ($keyword) {
                $q->where('code', 'like', "%{$keyword}%")
                  ->orWhere('name', 'like', "%{$keyword}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $now = Carbon::now();
            $status = $request->input('status');

            switch ($status) {
                case 'active': 
                    $query->where('status', 'active')
                          ->where(function ($q) use ($now) {
                              $q->whereNull('start_date')->orWhere('start_date', '<=', $now);
                          })
                          ->where(function ($q) use ($now) {
                              $q->whereNull('end_date')->orWhere('end_date', '>=', $now);
                          })
                          ->where(function ($q) {
                              $q->whereNull('usage_limit')
                                ->orWhereRaw('used_count < usage_limit');
                          });
                    break;
                case 'expired':
                    $query->where(function ($q) use ($now) {
                        $q->where('status', 'expired')
                          ->orWhere(function ($sub) use ($now) {
                              $sub->whereNotNull('end_date')->where('end_date', '<', $now);
                          });
                    });
                    break;
                case 'out_of_stock':
                    $query->whereNotNull('usage_limit')
                          ->whereRaw('used_count >= usage_limit');
                    break;
                case 'disabled':
                case 'inactive':
                    $query->where('status', 'disabled');
                    break;
                case 'upcoming':
                    $query->whereNotNull('start_date')->where('start_date', '>', $now);
                    break;
            }
        }

        $vouchers = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('admin.vouchers.index', compact('vouchers'));
    }

    /**
     * Show the form for creating a new system voucher.
     */
    public function create()
    {
        return view('admin.vouchers.create');
    }

    /**
     * Store a newly created system voucher in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:vouchers,code',
            'discount_type' => 'required|in:percent,fixed',
            'discount_value' => 'required|numeric|min:0',
            'min_booking_value' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'usage_limit' => 'nullable|integer|min:1',
            'max_uses_per_user' => 'nullable|integer|min:1',
            'apply_days' => 'nullable|array',
            'time_slots' => 'nullable|array',
            'target_user_input' => 'nullable|string',
        ]);

        try {
            $data = $request->all();
            $data['owner_id'] = null; // System voucher
            $data['applies_to_all_fields'] = true; // System voucher applies to all
            $data['is_system_voucher'] = true;

            if ($request->filled('target_user_input')) {
                $targetUserInput = $request->input('target_user_input');
                $targetUser = \App\Models\User::where('email', $targetUserInput)
                    ->orWhere('phone', $targetUserInput)
                    ->first();
                if (!$targetUser) {
                    return back()->withInput()->with('error', 'Không tìm thấy khách hàng nào với Email/Số điện thoại: ' . $targetUserInput);
                }
                $data['target_user_id'] = $targetUser->id;
            } else {
                $data['target_user_id'] = null;
            }

            $this->voucherService->create($data);

            return redirect()->route('admin.vouchers.index')
                ->with('success', 'Tạo mã giảm giá hệ thống thành công!');
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified system voucher details and statistics.
     */
    public function show($id)
    {
        try {
            $detail = $this->voucherService->getDetailForAdmin((int) $id);

            return view('admin.vouchers.show', [
                'voucher' => $detail['voucher'],
                'statistics' => $detail['statistics'],
                'usedBookings' => $detail['used_bookings'],
            ]);
        } catch (Exception $e) {
            return redirect()->route('admin.vouchers.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified system voucher.
     */
    public function edit($id)
    {
        $voucher = Voucher::where('id', $id)->whereNull('owner_id')->firstOrFail();
        $hasBeenUsed = $voucher->used_count > 0 || \Illuminate\Support\Facades\DB::table('booking_vouchers')->where('voucher_id', $id)->exists();
        $targetUserInput = $voucher->targetUser ? ($voucher->targetUser->email ?? $voucher->targetUser->phone) : '';

        return view('admin.vouchers.edit', compact('voucher', 'hasBeenUsed', 'targetUserInput'));
    }

    /**
     * Update the specified system voucher in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'discount_type' => 'nullable|in:percent,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'min_booking_value' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'usage_limit' => 'nullable|integer|min:0',
            'max_uses_per_user' => 'nullable|integer|min:1',
            'apply_days' => 'nullable|array',
            'time_slots' => 'nullable|array',
            'target_user_input' => 'nullable|string',
        ]);

        try {
            $voucher = Voucher::where('id', $id)->whereNull('owner_id')->firstOrFail();
            $data = $request->all();
            $data['applies_to_all_fields'] = true;
            $data['is_system_voucher'] = true;

            if ($request->filled('target_user_input')) {
                $targetUserInput = $request->input('target_user_input');
                $targetUser = \App\Models\User::where('email', $targetUserInput)
                    ->orWhere('phone', $targetUserInput)
                    ->first();
                if (!$targetUser) {
                    return back()->withInput()->with('error', 'Không tìm thấy khách hàng nào với Email/Số điện thoại: ' . $targetUserInput);
                }
                $data['target_user_id'] = $targetUser->id;
            } else {
                $data['target_user_id'] = null;
            }

            $voucher->update($data);

            // Handle apply_days and time_slots separately because they are stored as JSON/Arrays
            if ($request->has('apply_days')) {
                $voucher->apply_days = $data['apply_days'];
            }
            if ($request->has('time_slots')) {
                $voucher->time_slots = $data['time_slots'];
            }
            $voucher->save();

            return redirect()->route('admin.vouchers.index')
                ->with('success', 'Cập nhật voucher hệ thống thành công!');
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Toggle status (Enable / Disable) of a system voucher.
     */
    public function toggleStatus($id)
    {
        try {
            $voucher = Voucher::where('id', $id)->whereNull('owner_id')->firstOrFail();
            $voucher->status = $voucher->status === 'active' ? 'disabled' : 'active';
            $voucher->save();

            return back()->with('success', 'Trạng thái voucher hệ thống đã được cập nhật thành công.');
        } catch (Exception $e) {
            return back()->with('error', 'Không thể cập nhật trạng thái voucher.');
        }
    }

    /**
     * Remove the specified system voucher from storage.
     */
    public function destroy($id)
    {
        try {
            $voucher = Voucher::where('id', $id)->whereNull('owner_id')->firstOrFail();
            
            // Check if it has been used
            $hasBeenUsed = $voucher->used_count > 0 || \Illuminate\Support\Facades\DB::table('booking_vouchers')->where('voucher_id', $id)->exists();
            if ($hasBeenUsed) {
                return back()->with('error', 'Không thể xóa voucher đã có người sử dụng. Vui lòng tắt kích hoạt (disabled) thay vì xóa.');
            }

            $voucher->delete();
            return back()->with('success', 'Xóa voucher hệ thống thành công.');
        } catch (Exception $e) {
            return back()->with('error', 'Không thể xóa voucher này.');
        }
    }

    /**
     * Extend voucher end date and/or increase usage limit for admin.
     */
    public function extend(Request $request, $id)
    {
        $request->validate([
            'extend_days' => 'nullable|integer|min:1',
            'new_end_date' => 'nullable|date',
            'add_quantity' => 'nullable|integer|min:1',
            'new_usage_limit' => 'nullable|integer|min:0',
        ]);

        try {
            $this->voucherService->extendForAdmin((int) $id, $request->all());

            return back()->with('success', 'Gia hạn voucher thành công!');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
