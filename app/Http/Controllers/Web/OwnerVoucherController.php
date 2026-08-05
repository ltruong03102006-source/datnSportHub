<?php
// app/Http/Controllers/Web/OwnerVoucherController.php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Venue;
use App\Services\VoucherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            'apply_days.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
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
}
