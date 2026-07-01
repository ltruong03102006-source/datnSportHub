<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TransactionController extends Controller
{

    /**
     * Hiển thị lịch sử giao dịch của người dùng hiện tại.
     */
    public function index(Request $request): View
    {
        $query = Transaction::query()
            ->where('user_id', Auth::id())
            ->with(['booking.court.venue', 'user']);

        if ($searchCode = $request->input('search_code')) {
            $query->where('transaction_code', 'like', "%{$searchCode}%");
        }

        if ($searchBooking = $request->input('search_booking')) {
            $query->whereHas('booking', function ($bookingQuery) use ($searchBooking) {
                $bookingQuery->where('id', 'like', "%{$searchBooking}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('payment_status', $status);
        }

        if ($method = $request->input('payment_method')) {
            $query->where('payment_method', $method);
        }

        $transactions = $query
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $paymentMethods = Transaction::distinct()->pluck('payment_method')->filter()->values();

        return view('transactions.index', compact('transactions', 'paymentMethods'));
    }

    /**
     * Hiển thị chi tiết giao dịch nếu người dùng sở hữu hoặc là admin.
     */
    public function show(Transaction $transaction): View
    {
        Gate::authorize('view', $transaction);

        $transaction->load(['booking.court.venue', 'user']);

        return view('transactions.show', compact('transaction'));
    }
}
