<?php

namespace App\Policies;

use App\Models\Contract;
use App\Models\User;
use App\Services\ContractLifecycleService;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class ContractPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): Response
    {
        return in_array($user->role, ['admin', 'owner'], true)
            ? Response::allow()
            : Response::deny('Bạn không có quyền thực hiện thao tác này.');
    }

    public function view(User $user, Contract $contract): Response
    {
        if ($user->role === 'admin') {
            return Response::allow();
        }

        return $contract->owner_id === $user->id
            && in_array($contract->status, ContractLifecycleService::OWNER_VISIBLE_STATUSES, true)
            ? Response::allow()
            : Response::deny('Bạn không có quyền thực hiện thao tác này.');
    }

    public function create(User $user): Response
    {
        return $user->role === 'admin'
            ? Response::allow()
            : Response::deny('Bạn không có quyền thực hiện thao tác này.');
    }

    public function update(User $user, Contract $contract): Response
    {
        if ($user->role !== 'admin') {
            return Response::deny('Bạn không có quyền thực hiện thao tác này.');
        }

        return in_array($contract->status, ['draft', 'rejected'], true)
            ? Response::allow()
            : Response::deny('Bạn không có quyền thực hiện thao tác này.');
    }

    public function send(User $user, Contract $contract): Response
    {
        if ($user->role !== 'admin') {
            return Response::deny('Bạn không có quyền thực hiện thao tác này.');
        }

        return in_array($contract->status, ['draft', 'rejected'], true)
            ? Response::allow()
            : Response::deny('Bạn không có quyền thực hiện thao tác này.');
    }

    public function accept(User $user, Contract $contract): Response
    {
        if ($user->role !== 'owner') {
            return Response::deny('Bạn không có quyền thực hiện thao tác này.');
        }

        if ($contract->owner_id !== $user->id) {
            return Response::deny('Bạn không có quyền thực hiện thao tác này.');
        }

        return $contract->status === 'sent'
            ? Response::allow()
            : Response::deny('Bạn không có quyền thực hiện thao tác này.');
    }

    public function reject(User $user, Contract $contract): Response
    {
        if ($user->role !== 'owner') {
            return Response::deny('Bạn không có quyền thực hiện thao tác này.');
        }

        if ($contract->owner_id !== $user->id) {
            return Response::deny('Bạn không có quyền thực hiện thao tác này.');
        }

        return $contract->status === 'sent'
            ? Response::allow()
            : Response::deny('Bạn không có quyền thực hiện thao tác này.');
    }

    public function download(User $user, Contract $contract): Response
    {
        if ($user->role === 'admin') {
            return Response::allow();
        }

        return $contract->owner_id === $user->id
            && in_array($contract->status, ContractLifecycleService::OWNER_VISIBLE_STATUSES, true)
            ? Response::allow()
            : Response::deny('Bạn không có quyền thực hiện thao tác này.');
    }
    public function terminate(User $user, Contract $contract): Response
    {
        if ($user->role !== 'admin') {
            return Response::deny('Bạn không có quyền thực hiện thao tác này.');
        }

        // Chỉ cho phép chấm dứt khi hợp đồng đang ở trạng thái "Đã chấp nhận" (có hiệu lực)
        return $contract->status === 'accepted'
            ? Response::allow()
            : Response::deny('Chỉ có thể chấm dứt hợp đồng đang có hiệu lực.');
    }
}
