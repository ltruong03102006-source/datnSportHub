<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVenuePackageRequest;
use App\Http\Requests\UpdateVenuePackageRequest;
use App\Models\Venue;
use App\Models\VenuePackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OwnerVenuePackageController extends Controller
{
    public function index(Request $request): View
    {
        $venues = Venue::query()
            ->where('owner_id', $request->user()->id)
            ->with([
                'packages' => function ($query) {
                    $query->withCount([
                        'bookingPackages as active_booking_packages_count' => function ($subQuery) {
                            $subQuery->whereIn('status', [
                                'pending_payment',
                                'active',
                                'paused',
                            ]);
                        },
                    ])->latest();
                },
            ])
            ->latest()
            ->get();

        return view('owner.packages.index', compact('venues'));
    }

    public function create(Request $request, Venue $venue): View
    {
        $this->authorizeOwner($request, $venue);

        return view('owner.packages.create', compact('venue'));
    }

    public function store(StoreVenuePackageRequest $request, Venue $venue): RedirectResponse
    {
        $this->authorizeOwner($request, $venue);

        $data = $request->validated();

        $venue->packages()->create([
            'name' => $data['name'],
            'type' => $data['type'],
            'duration' => $data['duration'],
            'max_sessions_per_week' => $data['max_sessions_per_week'],
            'discount_percent' => $data['discount_percent'],
            'max_subscribers' => $data['max_subscribers'] ?? null,
            'status' => $data['status'],
        ]);

        return redirect()
            ->route('owner.web.packages.index')
            ->with('success', 'Đã tạo gói đặt sân thành công.');
    }

    public function edit(Request $request, Venue $venue, VenuePackage $package): View
    {
        $this->authorizeOwner($request, $venue);
        $this->ensurePackageBelongsToVenue($venue, $package);

        return view('owner.packages.edit', compact('venue', 'package'));
    }

    public function update(UpdateVenuePackageRequest $request, Venue $venue, VenuePackage $package): RedirectResponse
    {
        $this->authorizeOwner($request, $venue);
        $this->ensurePackageBelongsToVenue($venue, $package);

        $data = $request->validated();

        $package->update([
            'name' => $data['name'],
            'type' => $data['type'],
            'duration' => $data['duration'],
            'max_sessions_per_week' => $data['max_sessions_per_week'],
            'discount_percent' => $data['discount_percent'],
            'max_subscribers' => $data['max_subscribers'] ?? null,
            'status' => $data['status'],
        ]);

        return redirect()
            ->route('owner.web.packages.index')
            ->with('success', 'Đã cập nhật gói đặt sân.');
    }

    public function destroy(Request $request, Venue $venue, VenuePackage $package): RedirectResponse
    {
        $this->authorizeOwner($request, $venue);
        $this->ensurePackageBelongsToVenue($venue, $package);

        $hasRegisteredCustomers = $package->bookingPackages()
            ->whereIn('status', [
                'pending_payment',
                'active',
                'paused',
                'completed',
            ])
            ->exists();

        if ($hasRegisteredCustomers) {
            $package->update([
                'status' => 'inactive',
            ]);

            return back()->with(
                'success',
                'Gói đã có khách đăng ký nên không xóa trực tiếp. Hệ thống đã chuyển gói sang trạng thái tắt.'
            );
        }

        $package->delete();

        return back()->with('success', 'Đã xóa gói đặt sân.');
    }

    public function toggleVenue(Request $request, Venue $venue): RedirectResponse
    {
        $this->authorizeOwner($request, $venue);

        $isEnabled = ! (bool) $venue->allow_package_booking;

        $venue->update([
            'allow_package_booking' => $isEnabled,
        ]);

        return back()->with(
            'success',
            $isEnabled
                ? 'Đã bật chức năng đặt theo gói cho cơ sở.'
                : 'Đã tắt chức năng đặt theo gói cho cơ sở.'
        );
    }

    public function togglePackage(Request $request, Venue $venue, VenuePackage $package): RedirectResponse
    {
        $this->authorizeOwner($request, $venue);
        $this->ensurePackageBelongsToVenue($venue, $package);

        if (! $venue->allow_package_booking && $package->status !== 'active') {
            return back()->with(
                'error',
                'Cơ sở đang tắt chức năng đặt gói. Vui lòng bật chức năng đặt gói của cơ sở trước.'
            );
        }

        $package->update([
            'status' => $package->status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'Đã cập nhật trạng thái gói.');
    }

    private function authorizeOwner(Request $request, Venue $venue): void
    {
        abort_unless(
            (int) $venue->owner_id === (int) $request->user()->id,
            403
        );
    }

    private function ensurePackageBelongsToVenue(Venue $venue, VenuePackage $package): void
    {
        abort_unless(
            (int) $package->venue_id === (int) $venue->id,
            404
        );
    }
}