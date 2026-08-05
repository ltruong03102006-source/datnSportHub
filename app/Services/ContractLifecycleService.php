<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Venue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ContractLifecycleService
{
    public const OWNER_VISIBLE_STATUSES = ['sent', 'accepted', 'rejected', 'expired', 'terminated'];

    public function send(Contract $contract): Contract
    {
        return DB::transaction(function () use ($contract) {
            $lockedContract = Contract::query()->lockForUpdate()->findOrFail($contract->id);

            $lockedContract->update([
                'status' => 'sent',
                'sent_at' => now(),
                'rejection_reason' => null,
                'rejected_at' => null,
            ]);

            return $lockedContract->refresh();
        });
    }

    public function accept(Contract $contract, Request $request): array
{
    return DB::transaction(function () use ($contract, $request) {
        $lockedContract = Contract::query()->with(['venue', 'owner', 'creator'])->lockForUpdate()->findOrFail($contract->id);

        if ($this->isExpired($lockedContract)) {
            /* ... code cũ giữ nguyên ... */
        }

        // 1. SINH FILE PDF TĨNH NGAY KHI KÝ
        $pdf = Pdf::loadView('admin.contracts.partials.body', [ // Trỏ thẳng vào body hoặc tạo 1 file in pdf riêng
            'contract' => $lockedContract,
            'owner' => $lockedContract->owner,
            'venue' => $lockedContract->venue,
        ]);
        
        $fileName = 'HD-' . $lockedContract->contract_code . '-' . time() . '.pdf';
        $filePath = 'contracts/' . $fileName;
        
        // Lưu vào storage/app/public/contracts
        Storage::disk('public')->put($filePath, $pdf->output());

        // 2. CẬP NHẬT TRẠNG THÁI & BẰNG CHỨNG SỐ
        $lockedContract->update([
            'status' => 'accepted',
            'signed_at' => now(),
            'signed_ip' => $request->ip(),
            'signed_user_agent' => $request->userAgent(),
            'pdf_path' => $filePath,
            'rejection_reason' => null,
            'rejected_at' => null,
        ]);

        $activatedVenues = $this->isWithinActiveWindow($lockedContract)
            ? $this->activateVenuesFor($lockedContract)
            : 0;

        return [
            'contract' => $lockedContract->refresh(),
            'accepted' => true,
            'expired' => false,
            'activated_venues' => $activatedVenues,
        ];
    });
    }

    public function syncStatuses(): array
    {
        $expiredContracts = Contract::query()
            ->whereIn('status', ['sent', 'accepted'])
            ->whereDate('end_date', '<', today())
            ->get();

        foreach ($expiredContracts as $contract) {
            $contract->update([
                'status' => 'expired',
                'expired_at' => $contract->expired_at ?? now(),
            ]);
        }

        $activeContracts = Contract::query()
            ->where('status', 'accepted')
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today())
            ->get();

        $activatedVenues = 0;

        foreach ($activeContracts as $contract) {
            $activatedVenues += $this->activateVenuesFor($contract);
        }

        return [
            'expired_contracts' => $expiredContracts->count(),
            'activated_venues' => $activatedVenues,
        ];
    }

    public function isExpired(Contract $contract): bool
    {
        return $contract->end_date !== null && $contract->end_date->lt(today());
    }

    public function isWithinActiveWindow(Contract $contract): bool
    {
        if ($contract->start_date === null || $contract->end_date === null) {
            return false;
        }

        return $contract->start_date->lte(today()) && $contract->end_date->gte(today());
    }

    public function activateVenuesFor(Contract $contract): int
    {
        $contract->loadMissing('venue');

        if ($contract->venue) {
            if ((int) $contract->venue->owner_id !== (int) $contract->owner_id) {
                return 0;
            }

            $venues = collect([$contract->venue]);
        } else {
            $venues = Venue::where('owner_id', $contract->owner_id)
                ->whereIn('status', ['pending', 'approved', 'active'])
                ->get();
        }

        foreach ($venues as $venue) {
            $venue->update([
                'status' => 'active',
                'commission_rate' => $contract->commission_rate,
            ]);

            if (Schema::hasTable('venue_legal_documents')) {
                $venue->legalDocument()?->update([
                    'status' => 'approved',
                    'reviewed_by' => $contract->created_by,
                    'reviewed_at' => now(),
                    'reject_reason' => null,
                ]);
            }
        }

        return $venues->count();
    }
}
