<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CancellationPolicy;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth; // THÊM DÒNG KHAI BÁO NÀY CHO VS CODE HIỂU

class OwnerCancellationPolicyController extends Controller
{
    // API: Lấy danh sách
    public function index(Venue $venue)
    {
        // Thay auth()->id() bằng Auth::id()
        if ($venue['owner_id'] !== Auth::id()) abort(403);
        
        return response()->json(
            $venue->cancellationPolicies()->orderByDesc('hours_before')->get()
        );
    }

    // API: Thêm mốc mới
    public function store(Request $request, Venue $venue)
    {
        // Thay auth()->id() bằng Auth::id()
        if ($venue['owner_id'] !== Auth::id()) abort(403);

        $request->validate([
            'hours_before' => [
                'required', 'integer', 'min:0',
                Rule::unique('cancellation_policies')->where(function ($query) use ($venue) {
                    return $query->where('venue_id', $venue['id']);
                })
            ],
            'fee_percent' => ['required', 'integer', 'min:0', 'max:100'],
        ], [
            'hours_before.unique' => 'Mốc thời gian này đã tồn tại trong chính sách của bạn.'
        ]);

        $hours = $request->hours_before;
        $fee = $request->fee_percent;

        // Chỉ kiểm tra mốc SÁT GIỜ hơn gần nhất
        $closerPolicy = $venue->cancellationPolicies()->where('hours_before', '<', $hours)->orderByDesc('hours_before')->first();
        if ($closerPolicy && $fee >= $closerPolicy['fee_percent']) { 
            return response()->json([
                'errors' => ['fee_percent' => ["Lỗi: Mốc $hours giờ phải có phí phạt NHỎ HƠN {$closerPolicy['fee_percent']}% (Vì mốc sát giờ hơn là {$closerPolicy['hours_before']}h đang phạt {$closerPolicy['fee_percent']}%)."]]
            ], 422);
        }
        
        // Chỉ kiểm tra mốc XA GIỜ hơn gần nhất
        $furtherPolicy = $venue->cancellationPolicies()->where('hours_before', '>', $hours)->orderBy('hours_before', 'asc')->first();
        if ($furtherPolicy && $fee <= $furtherPolicy['fee_percent']) { 
            return response()->json([
                'errors' => ['fee_percent' => ["Lỗi: Mốc $hours giờ phải có phí phạt LỚN HƠN {$furtherPolicy['fee_percent']}% (Vì mốc báo sớm hơn là {$furtherPolicy['hours_before']}h đang phạt {$furtherPolicy['fee_percent']}%)."]]
            ], 422);
        }

        $policy = $venue->cancellationPolicies()->create($request->only('hours_before', 'fee_percent'));
        
        return response()->json(['message' => 'Đã thêm chính sách thành công!', 'data' => $policy]);
    }

    // API: Xóa mốc
    public function destroy(Venue $venue, CancellationPolicy $policy)
    {
        // Thay auth()->id() bằng Auth::id()
        if ($venue['owner_id'] !== Auth::id() || $policy['venue_id'] !== $venue['id']) abort(403);
        
        $policy->delete();
        return response()->json(['message' => 'Đã xóa chính sách!']);
    }
}