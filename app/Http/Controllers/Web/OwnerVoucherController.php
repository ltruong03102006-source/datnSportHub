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
use Illuminate\Support\Facades\DB;

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
            'max_uses_per_user' => 'nullable|integer|min:1',
            'apply_days' => 'nullable|array',
            'time_slots' => 'nullable|array',
            'applies_to_all_fields' => 'nullable|boolean',
            'venue_ids' => 'nullable|array',
            'venue_ids.*' => 'exists:venues,id',
            'target_user_input' => 'nullable|string',
        ]);

        try {
            $data = $request->all();
            $data['owner_id'] = Auth::id();
            $data['applies_to_all_fields'] = $request->has('applies_to_all_fields');

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
        $targetUserInput = $voucher->targetUser ? ($voucher->targetUser->email ?? $voucher->targetUser->phone) : '';

        return view('owner.vouchers.edit', compact('voucher', 'venues', 'hasBeenUsed', 'targetUserInput'));
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
            'max_uses_per_user' => 'nullable|integer|min:1',
            'apply_days' => 'nullable|array',
            'time_slots' => 'nullable|array',
            'applies_to_all_fields' => 'nullable|boolean',
            'venue_ids' => 'nullable|array',
            'venue_ids.*' => 'exists:venues,id',
            'target_user_input' => 'nullable|string',
        ]);

        try {
            $data = $request->all();
            $data['applies_to_all_fields'] = $request->has('applies_to_all_fields');

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

    /**
     * Extend voucher end date and/or increase usage limit.
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
            $this->voucherService->extendForOwner((int) $id, Auth::id(), $request->all());

            return back()->with('success', 'Gia hạn voucher thành công!');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display voucher effectiveness report and analytics charts.
     */
    public function report(Request $request)
    {
        $ownerId = Auth::id();
        $monthYear = $request->input('month_year', Carbon::now()->format('Y-m'));
        $venueId = $request->input('venue_id');

        // Get owner venues for filter
        $venues = Venue::where('owner_id', $ownerId)->get();

        // 1. Basic Stats
        $vouchersQuery = Voucher::where('owner_id', $ownerId);
        if ($venueId) {
            $vouchersQuery->where(function ($q) use ($venueId) {
                $q->whereHas('venues', function ($vq) use ($venueId) {
                    $vq->where('venues.id', $venueId);
                })->orWhere('applies_to_all_fields', true);
            });
        }
        $totalVouchers = $vouchersQuery->count();

        // Base query for bookings with applied vouchers
        $bookingsWithVoucherQuery = DB::table('bookings')
            ->join('booking_vouchers', 'bookings.id', '=', 'booking_vouchers.booking_id')
            ->join('vouchers', 'booking_vouchers.voucher_id', '=', 'vouchers.id')
            ->where('vouchers.owner_id', $ownerId)
            ->whereNotIn('bookings.status', ['cancelled', 'rejected']);

        if ($monthYear) {
            $bookingsWithVoucherQuery->whereRaw("DATE_FORMAT(bookings.created_at, '%Y-%m') = ?", [$monthYear]);
        }
        if ($venueId) {
            $bookingsWithVoucherQuery->join('courts', 'bookings.court_id', '=', 'courts.id')
                ->where('courts.venue_id', $venueId);
        }

        // Clone queries for specific calculations
        $totalUses = (clone $bookingsWithVoucherQuery)->count();
        $totalDiscount = (clone $bookingsWithVoucherQuery)->sum('booking_vouchers.discount_amount');
        $totalRevenue = (clone $bookingsWithVoucherQuery)->sum('bookings.total_price');
        $avgDiscount = $totalUses > 0 ? $totalDiscount / $totalUses : 0;

        // 2. Rankings: Most & Least Effective Vouchers
        $voucherStatsQuery = DB::table('booking_vouchers')
            ->join('vouchers', 'booking_vouchers.voucher_id', '=', 'vouchers.id')
            ->join('bookings', 'booking_vouchers.booking_id', '=', 'bookings.id')
            ->select(
                'vouchers.id',
                'vouchers.code',
                'vouchers.name',
                DB::raw('count(bookings.id) as uses'),
                DB::raw('sum(booking_vouchers.discount_amount) as total_discount')
            )
            ->where('vouchers.owner_id', $ownerId)
            ->whereNotIn('bookings.status', ['cancelled', 'rejected']);

        if ($monthYear) {
            $voucherStatsQuery->whereRaw("DATE_FORMAT(bookings.created_at, '%Y-%m') = ?", [$monthYear]);
        }
        if ($venueId) {
            $voucherStatsQuery->join('courts', 'bookings.court_id', '=', 'courts.id')
                ->where('courts.venue_id', $venueId);
        }

        $voucherStats = $voucherStatsQuery->groupBy('vouchers.id', 'vouchers.code', 'vouchers.name')
            ->orderBy('uses', 'desc')
            ->get();

        $mostEffective = $voucherStats->take(5);
        
        $allOwnerVouchers = $vouchersQuery->get();
        $leastEffective = $allOwnerVouchers->map(function($v) use ($voucherStats) {
            $stat = $voucherStats->firstWhere('id', $v->id);
            return (object) [
                'id' => $v->id,
                'code' => $v->code,
                'name' => $v->name,
                'uses' => $stat ? $stat->uses : 0,
                'total_discount' => $stat ? $stat->total_discount : 0,
            ];
        })->sortBy('uses')->take(5);

        // 3. Line Chart: Daily Usage
        $dailyUsageQuery = DB::table('bookings')
            ->join('booking_vouchers', 'bookings.id', '=', 'booking_vouchers.booking_id')
            ->join('vouchers', 'booking_vouchers.voucher_id', '=', 'vouchers.id')
            ->select(DB::raw("DATE_FORMAT(bookings.created_at, '%d/%m') as day_label"), DB::raw('count(*) as count'))
            ->where('vouchers.owner_id', $ownerId)
            ->whereNotIn('bookings.status', ['cancelled', 'rejected']);

        if ($monthYear) {
            $dailyUsageQuery->whereRaw("DATE_FORMAT(bookings.created_at, '%Y-%m') = ?", [$monthYear]);
        }
        if ($venueId) {
            $dailyUsageQuery->join('courts', 'bookings.court_id', '=', 'courts.id')
                ->where('courts.venue_id', $venueId);
        }

        $dailyUsage = $dailyUsageQuery->groupBy('day_label')
            ->orderBy('day_label', 'asc')
            ->get();

        $dailyLabels = $dailyUsage->pluck('day_label')->toArray();
        $dailyValues = $dailyUsage->pluck('count')->toArray();

        // 4. Doughnut Chart: Voucher vs No-Voucher Revenue
        $noVoucherQuery = DB::table('bookings')
            ->join('courts', 'bookings.court_id', '=', 'courts.id')
            ->join('venues', 'courts.venue_id', '=', 'venues.id')
            ->where('venues.owner_id', $ownerId)
            ->whereNotIn('bookings.status', ['cancelled', 'rejected'])
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('booking_vouchers')
                    ->whereColumn('booking_vouchers.booking_id', 'bookings.id');
            });

        if ($monthYear) {
            $noVoucherQuery->whereRaw("DATE_FORMAT(bookings.created_at, '%Y-%m') = ?", [$monthYear]);
        }
        if ($venueId) {
            $noVoucherQuery->where('courts.venue_id', $venueId);
        }
        $noVoucherRevenue = $noVoucherQuery->sum('bookings.total_price');

        // 5. Bar Chart: Peak vs Normal Time Slots count
        $timeSlotQuery = DB::table('bookings')
            ->join('booking_vouchers', 'bookings.id', '=', 'booking_vouchers.booking_id')
            ->join('vouchers', 'booking_vouchers.voucher_id', '=', 'vouchers.id')
            ->leftJoin('slot_prices', function ($join) {
                $join->on('bookings.time_slot_id', '=', 'slot_prices.time_slot_id')
                     ->on(DB::raw('slot_prices.day_of_week'), '=', DB::raw('(DAYOFWEEK(bookings.slot_date) - 1)'));
            })
            ->select(DB::raw("COALESCE(slot_prices.price_type, 'normal') as calculated_price_type"), DB::raw('count(*) as count'))
            ->where('vouchers.owner_id', $ownerId)
            ->whereNotIn('bookings.status', ['cancelled', 'rejected']);

        if ($monthYear) {
            $timeSlotQuery->whereRaw("DATE_FORMAT(bookings.created_at, '%Y-%m') = ?", [$monthYear]);
        }
        if ($venueId) {
            $timeSlotQuery->join('courts', 'bookings.court_id', '=', 'courts.id')
                ->where('courts.venue_id', $venueId);
        }

        $timeSlotStats = $timeSlotQuery->groupBy(DB::raw("COALESCE(slot_prices.price_type, 'normal')"))->get();
        $peakCount = $timeSlotStats->firstWhere('calculated_price_type', 'peak')?->count ?? 0;
        $normalCount = $timeSlotStats->firstWhere('calculated_price_type', 'normal')?->count ?? 0;

        // 6. Voucher usage limit fill rates (Conversion/Usage rate)
        $limitRates = $allOwnerVouchers->filter(fn($v) => !is_null($v->usage_limit) && $v->usage_limit > 0)
            ->map(function($v) {
                return (object) [
                    'code' => $v->code,
                    'rate' => round(($v->used_count / $v->usage_limit) * 100, 1)
                ];
            })->values()->take(8);

        return view('owner.vouchers.report', compact(
            'venues',
            'monthYear',
            'venueId',
            'totalVouchers',
            'totalUses',
            'totalDiscount',
            'totalRevenue',
            'avgDiscount',
            'mostEffective',
            'leastEffective',
            'dailyLabels',
            'dailyValues',
            'noVoucherRevenue',
            'peakCount',
            'normalCount',
            'limitRates'
        ));
    }
}
