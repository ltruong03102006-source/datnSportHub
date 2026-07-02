<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminTransactionController extends Controller
{

    /**
     * Hiển thị toàn bộ lịch sử giao dịch cho admin.
     */
    public function index(Request $request): View
    {
        $query = Transaction::query()->with(['booking.court.venue', 'bookingPackage.venue', 'bookingPackage.package', 'user']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_code', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('booking', function ($bookingQuery) use ($search) {
                        $bookingQuery->where('id', 'like', "%{$search}%");
                    })
                    ->orWhereHas('bookingPackage', function ($packageQuery) use ($search) {
                        $packageQuery->where('id', 'like', "%{$search}%");
                    });
            });
        }

        if ($status = $request->input('status')) {
            $query->where('payment_status', $status);
        }

        if ($method = $request->input('payment_method')) {
            $query->where('payment_method', $method);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('transaction_time', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('transaction_time', '<=', $dateTo);
        }

        if ($month = $request->input('month')) {
            $query->whereMonth('transaction_time', $month);
        }

        if ($year = $request->input('year')) {
            $query->whereYear('transaction_time', $year);
        }

        $sort = $request->input('sort', 'desc');
        $transactions = $query
            ->orderBy('created_at', $sort === 'asc' ? 'asc' : 'desc')
            ->paginate(15)
            ->withQueryString();

        $paymentMethods = Transaction::distinct()->pluck('payment_method')->filter()->values();

        return view('admin.transactions.index', compact('transactions', 'paymentMethods'));
    }

    /**
     * Hiển thị chi tiết giao dịch cho admin.
     */
    public function show(Transaction $transaction): View
    {
        $transaction->load(['booking.court.venue', 'bookingPackage.venue', 'bookingPackage.package', 'user']);

        return view('admin.transactions.show', compact('transaction'));
    }
}
