<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BookingPackage;
use App\Models\VenuePackage;
use Illuminate\View\View;

class AdminPackageController extends Controller
{
    public function index(): View
    {
        $paidStatuses = [
            'active',
            'paused',
            'completed',
        ];

        $packages = VenuePackage::query()
            ->with([
                'venue.owner',
            ])
            ->withCount([
                'bookingPackages',

                'bookingPackages as pending_payment_count' => function ($query) {
                    $query->where('status', 'pending_payment');
                },

                'bookingPackages as active_subscribers_count' => function ($query) {
                    $query->whereIn('status', [
                        'active',
                        'paused',
                    ]);
                },

                'bookingPackages as completed_subscribers_count' => function ($query) {
                    $query->where('status', 'completed');
                },

                'bookingPackages as cancelled_subscribers_count' => function ($query) {
                    $query->whereIn('status', [
                        'cancelled',
                        'expired',
                    ]);
                },
            ])
            ->withSum([
                'bookingPackages as revenue' => function ($query) use ($paidStatuses) {
                    $query->whereIn('status', $paidStatuses);
                },
            ], 'final_amount')
            ->latest()
            ->paginate(15);

        $stats = [
            'total_packages' => VenuePackage::query()->count(),

            'active_packages' => VenuePackage::query()
                ->where('status', 'active')
                ->count(),

            'inactive_packages' => VenuePackage::query()
                ->where('status', 'inactive')
                ->count(),

            'pending_payment_packages' => BookingPackage::query()
                ->where('status', 'pending_payment')
                ->count(),

            'active_booking_packages' => BookingPackage::query()
                ->where('status', 'active')
                ->count(),

            'paused_booking_packages' => BookingPackage::query()
                ->where('status', 'paused')
                ->count(),

            'completed_booking_packages' => BookingPackage::query()
                ->where('status', 'completed')
                ->count(),

            'cancelled_booking_packages' => BookingPackage::query()
                ->whereIn('status', [
                    'cancelled',
                    'expired',
                ])
                ->count(),

            'registered_users' => BookingPackage::query()
                ->distinct('user_id')
                ->count('user_id'),

            'paid_users' => BookingPackage::query()
                ->whereIn('status', $paidStatuses)
                ->distinct('user_id')
                ->count('user_id'),

            'package_revenue' => BookingPackage::query()
                ->whereIn('status', $paidStatuses)
                ->sum('final_amount'),

            'pending_revenue' => BookingPackage::query()
                ->where('status', 'pending_payment')
                ->sum('final_amount'),

            'total_original_amount' => BookingPackage::query()
                ->whereIn('status', $paidStatuses)
                ->sum('total_amount'),

            'total_discount_amount' => BookingPackage::query()
                ->whereIn('status', $paidStatuses)
                ->sum('discount_amount'),
        ];

        return view('admin.packages.index', compact('packages', 'stats'));
    }
}