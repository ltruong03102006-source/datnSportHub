<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Venue;
use App\Models\Voucher;
use App\Services\VoucherService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class OwnerVoucherController extends Controller
{
    protected VoucherService $voucherService;

    public function __construct(VoucherService $voucherService)
    {
        $this->voucherService = $voucherService;
    }

    /**
     * Display a listing of vouchers for the logged-in owner.
     */
    public function index(Request $request)
    {
        $ownerId = Auth::id();

        // Get owner venues for filter
        $venues = Venue::where('owner_id', $ownerId)->get();

        $query = Voucher::with(['venues'])
            ->where('owner_id', $ownerId);

        // Filter by venue
        if ($request->filled('venue_id')) {
            $venueId = $request->input('venue_id');
            $query->where(function ($q) use ($venueId) {
                $q->whereHas('venues', function ($vq) use ($venueId) {
                    $vq->where('venues.id', $venueId);
                })->orWhere('sport_field_id', $venueId);
            });
        }

        // Filter by search keyword (code or name)
        if ($request->filled('keyword')) {
            $keyword = trim($request->input('keyword'));
            $query->where(function ($q) use ($keyword) {
                $q->where('code', 'like', "%{$keyword}%")
                  ->orWhere('name', 'like', "%{$keyword}%");
            });
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('start_date', '>=', $request->input('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->where('end_date', '<=', $request->input('end_date') . ' 23:59:59');
        }

        // Filter by status
        if ($request->filled('status')) {
            $now = Carbon::now();
            $status = $request->input('status');

            switch ($status) {
                case 'active': // Đang áp dụng
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

                case 'expired': // Hết hạn
                    $query->where(function ($q) use ($now) {
                        $q->where('status', 'expired')
                          ->orWhere(function ($sub) use ($now) {
                              $sub->whereNotNull('end_date')->where('end_date', '<', $now);
                          });
                    });
                    break;

                case 'out_of_stock': // Hết lượt
                    $query->whereNotNull('usage_limit')
                          ->whereRaw('used_count >= usage_limit');
                    break;

                case 'disabled': // Chưa kích hoạt / Tắt
                case 'inactive':
                    $query->where('status', 'disabled');
                    break;

                case 'upcoming': // Sắp áp dụng
                    $query->whereNotNull('start_date')->where('start_date', '>', $now);
                    break;
            }
        }

        $vouchers = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('owner.vouchers.index', compact('vouchers', 'venues'));
    }

    /**
     * Show the form for creating a new voucher.
     */
    public function create()
    {
        $ownerId = Auth::id();
        $venues = Venue::where('owner_id', $ownerId)->get();

        return view('owner.vouchers.create', compact('venues'));
    }

    /**
     * Store a newly created voucher in storage.
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
            'applies_to_all_fields' => 'nullable|boolean',
            'venue_ids' => 'nullable|array',
            'venue_ids.*' => 'exists:venues,id',
        ]);

        try {
            $data = $request->all();
            $data['owner_id'] = Auth::id();
            $data['applies_to_all_fields'] = $request->has('applies_to_all_fields');

            $this->voucherService->create($data);

            return redirect()->route('owner.web.vouchers.index')
                ->with('success', 'Tạo mã giảm giá thành công!');
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified voucher details and statistics.
     */
    public function show($id)
    {
        try {
            $ownerId = Auth::id();
            $detail = $this->voucherService->getDetailForOwner((int) $id, $ownerId);

            return view('owner.vouchers.show', [
                'voucher' => $detail['voucher'],
                'statistics' => $detail['statistics'],
                'usedBookings' => $detail['used_bookings'],
            ]);
        } catch (Exception $e) {
            return redirect()->route('owner.web.vouchers.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified voucher.
     */
    public function edit($id)
    {
        $ownerId = Auth::id();
        $voucher = Voucher::with('venues')->where('id', $id)->where('owner_id', $ownerId)->firstOrFail();
        $venues = Venue::where('owner_id', $ownerId)->get();

        $hasBeenUsed = $voucher->used_count > 0 || \Illuminate\Support\Facades\DB::table('booking_vouchers')->where('voucher_id', $id)->exists();

        return view('owner.vouchers.edit', compact('voucher', 'venues', 'hasBeenUsed'));
    }

    /**
     * Update the specified voucher in storage.
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
            'applies_to_all_fields' => 'nullable|boolean',
            'venue_ids' => 'nullable|array',
            'venue_ids.*' => 'exists:venues,id',
        ]);

        try {
            $data = $request->all();
            $data['applies_to_all_fields'] = $request->has('applies_to_all_fields');

            $this->voucherService->updateForOwner((int) $id, Auth::id(), $data);

            return redirect()->route('owner.web.vouchers.index')
                ->with('success', 'Cập nhật voucher thành công!');
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Toggle status (Enable / Disable) of a voucher.
     */
    public function toggleStatus($id)
    {
        try {
            $voucher = Voucher::where('id', $id)->where('owner_id', Auth::id())->firstOrFail();
            $voucher->status = $voucher->status === 'active' ? 'disabled' : 'active';
            $voucher->save();

            return back()->with('success', 'Trạng thái voucher đã được cập nhật thành công.');
        } catch (Exception $e) {
            return back()->with('error', 'Không thể cập nhật trạng thái voucher.');
        }
    }

    /**
     * Remove the specified voucher from storage.
     */
    public function destroy($id)
    {
        try {
            $this->voucherService->delete((int) $id);
            return back()->with('success', 'Xóa voucher thành công.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
