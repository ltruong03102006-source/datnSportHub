<?php
// app/Http/Controllers/Web/OwnerVoucherController.php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Venue;
use App\Services\VoucherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Voucher;

class OwnerVoucherController extends Controller
{
    protected $voucherService;

    public function __construct(VoucherService $voucherService)
    {
        $this->voucherService = $voucherService;
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $ownerId = Auth::id();
        $venues = Venue::where('owner_id', $ownerId)->get();

        $query = Voucher::with('venues')
            ->where('owner_id', $ownerId);

        // Search by code or name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // Filter by venue
        if ($request->filled('venue_id')) {
            $venueId = $request->venue_id;
            $query->whereHas('venues', function($q) use ($venueId) {
                $q->where('venues.id', $venueId);
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $status = $request->status;
            $now = now();
            switch ($status) {
                case 'active':
                    $query->where('status', 'active')
                          ->where(function($q) use ($now) {
                              $q->whereNull('start_date')->orWhere('start_date', '<=', $now);
                          })
                          ->where(function($q) use ($now) {
                              $q->whereNull('end_date')->orWhere('end_date', '>=', $now);
                          })
                          ->where(function($q) {
                              $q->whereNull('usage_limit')
                                ->orWhereColumn('used_count', '<', 'usage_limit');
                          });
                    break;
                case 'used_up':
                    $query->whereNotNull('usage_limit')
                          ->whereColumn('used_count', '>=', 'usage_limit');
                    break;
                case 'expired':
                    $query->whereNotNull('end_date')->where('end_date', '<', $now);
                    break;
                case 'pending':
                    $query->whereNotNull('start_date')->where('start_date', '>', $now)
                          ->where('status', 'active');
                    break;
            }
        }

        // Filter by time
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $vouchers = $query->latest()->paginate(10)->withQueryString();

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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:vouchers,code',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'min_booking_value' => 'nullable|numeric|min:0',
            'usage_limit' => 'required|integer|min:1',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'apply_days' => 'nullable|array',
            'apply_days.*' => 'in:0,1,2,3,4,5,6,monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'venue_ids' => 'required|array|min:1',
            'venue_ids.*' => 'exists:venues,id',
        ]);

        try {
            // Add owner_id to validated data
            $validated['owner_id'] = Auth::id();
            
            // Map time slots if present
            if (!empty($validated['start_time']) && !empty($validated['end_time'])) {
                $validated['time_slots'] = [[
                    'start' => $validated['start_time'],
                    'end' => $validated['end_time']
                ]];
            }

            $voucher = $this->voucherService->create($validated);

            return redirect()
                ->route('owner.web.vouchers.create')
                ->with('success', 'Voucher đã được tạo thành công! Mã: ' . $voucher->code);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra khi tạo voucher: ' . $e->getMessage());
        }
    }

    /**
     * Display voucher efficiency report for owner.
     */
    public function report(Request $request)
    {
        $ownerId = Auth::id();
        $venues = Venue::where('owner_id', $ownerId)->get();

        $venueId = $request->input('venue_id');
        $monthYear = $request->input('month_year', date('Y-m'));

        $parts = explode('-', $monthYear);
        $year = (int) ($parts[0] ?? date('Y'));
        $month = (int) ($parts[1] ?? date('m'));

        // Owner vouchers
        $voucherQuery = Voucher::where('owner_id', $ownerId);
        if ($venueId) {
            $voucherQuery->whereHas('venues', function($q) use ($venueId) {
                $q->where('venues.id', $venueId);
            });
        }
        $vouchers = $voucherQuery->get();
        $voucherIds = $vouchers->pluck('id')->toArray();

        $totalVouchers = count($vouchers);

        // Bookings using owner vouchers
        $bookingVouchersQuery = DB::table('booking_vouchers')
            ->join('bookings', 'booking_vouchers.booking_id', '=', 'bookings.id')
            ->join('vouchers', 'booking_vouchers.voucher_id', '=', 'vouchers.id')
            ->whereIn('booking_vouchers.voucher_id', $voucherIds)
            ->whereNotIn('bookings.status', ['cancelled', 'rejected'])
            ->whereYear('bookings.created_at', $year)
            ->whereMonth('bookings.created_at', $month);

        if ($venueId) {
            $bookingVouchersQuery->join('courts', 'bookings.court_id', '=', 'courts.id')
                ->where('courts.venue_id', $venueId);
        }

        $usedRecords = $bookingVouchersQuery->select(
            'booking_vouchers.*',
            'bookings.total_price',
            'bookings.created_at as booking_created_at',
            'bookings.start_time',
            'vouchers.code',
            'vouchers.name as voucher_name'
        )->get();

        $totalUses = $usedRecords->count();
        $totalDiscount = (float) $usedRecords->sum('discount_amount');
        $totalRevenue = (float) $usedRecords->sum('total_price');
        $avgDiscount = $totalUses > 0 ? $totalDiscount / $totalUses : 0;

        // Effective / Least Effective
        $groupedByVoucher = $usedRecords->groupBy('voucher_id')->map(function ($items) {
            $first = $items->first();
            return (object)[
                'code' => $first->code,
                'name' => $first->voucher_name,
                'uses' => $items->count(),
                'total_discount' => (float) $items->sum('discount_amount'),
            ];
        })->sortByDesc('uses');

        $mostEffective = $groupedByVoucher->take(5);
        $leastEffective = $groupedByVoucher->sortBy('uses')->take(5);

        // Daily usage
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $dailyLabels = [];
        $dailyValues = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dateStr = sprintf('%02d/%02d', $d, $month);
            $dailyLabels[] = $dateStr;
            $fullDateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
            $dailyValues[] = $usedRecords->filter(function($r) use ($fullDateStr) {
                return date('Y-m-d', strtotime($r->booking_created_at)) === $fullDateStr;
            })->count();
        }

        // Revenue without vouchers
        $noVoucherBookingQuery = DB::table('bookings')
            ->join('courts', 'bookings.court_id', '=', 'courts.id')
            ->join('venues', 'courts.venue_id', '=', 'venues.id')
            ->where('venues.owner_id', $ownerId)
            ->whereNotIn('bookings.status', ['cancelled', 'rejected'])
            ->whereYear('bookings.created_at', $year)
            ->whereMonth('bookings.created_at', $month)
            ->whereNotIn('bookings.id', function($sub) {
                $sub->select('booking_id')->from('booking_vouchers');
            });

        if ($venueId) {
            $noVoucherBookingQuery->where('venues.id', $venueId);
        }
        $noVoucherRevenue = (float) $noVoucherBookingQuery->sum('bookings.total_price');

        // Peak / Normal hours
        $peakCount = 0;
        $normalCount = 0;
        foreach ($usedRecords as $rec) {
            $time = $rec->start_time ?? date('H:i', strtotime($rec->booking_created_at));
            $hour = (int) substr($time, 0, 2);
            if ($hour >= 17 && $hour <= 22) {
                $peakCount++;
            } else {
                $normalCount++;
            }
        }

        // Limit rates
        $limitRates = $vouchers->filter(fn($v) => !is_null($v->usage_limit) && $v->usage_limit > 0)->map(function($v) {
            return [
                'code' => $v->code,
                'rate' => round(($v->used_count / $v->usage_limit) * 100, 1),
            ];
        })->values();

        return view('owner.vouchers.report', compact(
            'venues', 'venueId', 'monthYear', 'totalVouchers', 'totalUses',
            'totalRevenue', 'totalDiscount', 'avgDiscount', 'mostEffective',
            'leastEffective', 'dailyLabels', 'dailyValues', 'noVoucherRevenue',
            'peakCount', 'normalCount', 'limitRates'
        ));
    }

    /**
     * Display details of a specific voucher.
     */
    public function show($id)
    {
        $ownerId = Auth::id();
        try {
            $data = $this->voucherService->getDetailForOwner((int)$id, $ownerId);
            return view('owner.vouchers.show', [
                'voucher' => $data['voucher'],
                'statistics' => $data['statistics'],
                'usedBookings' => $data['used_bookings'],
            ]);
        } catch (\Exception $e) {
            return redirect()->route('owner.web.vouchers.index')->with('error', $e->getMessage());
        }
    }

    /**
     * Show edit form for voucher.
     */
    public function edit($id)
    {
        $ownerId = Auth::id();
        $voucher = Voucher::where('id', $id)->where('owner_id', $ownerId)->firstOrFail();
        $venues = Venue::where('owner_id', $ownerId)->get();
        $hasBeenUsed = $voucher->used_count > 0 || DB::table('booking_vouchers')->where('voucher_id', $id)->exists();

        return view('owner.vouchers.edit', compact('voucher', 'venues', 'hasBeenUsed'));
    }

    /**
     * Update voucher.
     */
    public function update(Request $request, $id)
    {
        $ownerId = Auth::id();
        try {
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'end_date' => 'nullable|date',
                'usage_limit' => 'nullable|integer|min:0',
                'discount_type' => 'nullable|string|in:percentage,fixed',
                'discount_value' => 'nullable|numeric|min:0',
                'max_discount_amount' => 'nullable|numeric|min:0',
                'min_booking_value' => 'nullable|numeric|min:0',
                'venue_ids' => 'nullable|array',
            ]);

            $this->voucherService->updateForOwner((int)$id, $ownerId, $validated);

            return redirect()->route('owner.web.vouchers.index')->with('success', 'Cập nhật voucher thành công!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Extend voucher end date or add usages.
     */
    public function extend(Request $request, $id)
    {
        $ownerId = Auth::id();
        try {
            $this->voucherService->extendForOwner((int)$id, $ownerId, $request->all());
            return redirect()->back()->with('success', 'Gia hạn / bổ sung lượt dùng voucher thành công!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Toggle voucher status.
     */
    public function toggleStatus(Request $request, $id)
    {
        $ownerId = Auth::id();
        $voucher = Voucher::where('id', $id)->where('owner_id', $ownerId)->firstOrFail();
        $voucher->status = $voucher->status === 'active' ? 'disabled' : 'active';
        $voucher->save();

        return redirect()->back()->with('success', 'Đã thay đổi trạng thái voucher thành công!');
    }

    /**
     * Delete voucher.
     */
    public function destroy($id)
    {
        $ownerId = Auth::id();
        try {
            $voucher = Voucher::where('id', $id)->where('owner_id', $ownerId)->firstOrFail();
            $this->voucherService->delete($voucher->id);
            return redirect()->route('owner.web.vouchers.index')->with('success', 'Đã xóa voucher thành công!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
