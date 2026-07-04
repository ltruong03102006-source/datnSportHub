<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Transaction;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TransactionController extends Controller
{
    private const VISIBLE_PAYMENT_STATUSES = ['success', 'paid', 'completed', 'refunded'];

    /**
     * Hiển thị lịch sử giao dịch của người dùng hiện tại.
     */
    public function index(Request $request): View
    {
        $transactionQuery = Transaction::query()
            ->where('user_id', Auth::id())
            ->whereIn('payment_status', self::VISIBLE_PAYMENT_STATUSES)
            ->with(['booking.court.venue', 'bookingPackage.venue', 'bookingPackage.package', 'user']);

        if ($searchCode = $request->input('search_code')) {
            $transactionQuery->where('transaction_code', 'like', "%{$searchCode}%");
        }

        if ($searchBooking = $request->input('search_booking')) {
            $transactionQuery->where(function ($searchQuery) use ($searchBooking) {
                $searchQuery
                    ->whereHas('booking', function ($bookingQuery) use ($searchBooking) {
                        $bookingQuery->where('id', 'like', "%{$searchBooking}%");
                    })
                    ->orWhereHas('bookingPackage', function ($packageQuery) use ($searchBooking) {
                        $packageQuery->where('id', 'like', "%{$searchBooking}%");
                    });
            });
        }

        if ($status = $request->input('status')) {
            $transactionQuery->whereIn('payment_status', $this->statusFilterValues($status));
        }

        if ($method = $request->input('payment_method')) {
            $transactionQuery->where('payment_method', $method);
        }

        $transactionsFromTable = $transactionQuery
            ->orderByDesc('created_at')
            ->get();

        $bookingQuery = Booking::query()
            ->where('user_id', Auth::id())
            ->whereNull('booking_package_id')
            ->whereIn('payment_status', ['paid', 'refunded'])
            ->whereDoesntHave('transactions')
            ->with(['court.venue', 'user'])
            ->orderByDesc('created_at');

        if ($searchBooking = $request->input('search_booking')) {
            $bookingQuery->where('id', 'like', "%{$searchBooking}%");
        }

        if ($status = $request->input('status')) {
            $bookingQuery->whereIn('payment_status', $status === 'refunded' ? ['refunded'] : ['paid']);
        }

        $virtualTransactions = $bookingQuery
            ->get()
            ->map(fn (Booking $booking) => $this->makeVirtualBookingTransaction($booking))
            ->filter(function (Transaction $transaction) use ($request) {
                if ($searchCode = $request->input('search_code')) {
                    if (! str_contains(strtolower((string) $transaction->transaction_code), strtolower($searchCode))) {
                        return false;
                    }
                }

                if ($method = $request->input('payment_method')) {
                    if ($transaction->payment_method !== $method) {
                        return false;
                    }
                }

                return true;
            });

        $mergedTransactions = $transactionsFromTable
            ->concat($virtualTransactions)
            ->sortByDesc(fn (Transaction $transaction) => optional($transaction->created_at)->timestamp ?? 0)
            ->values();

        $perPage = 10;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $transactions = new LengthAwarePaginator(
            $mergedTransactions->forPage($page, $perPage)->values(),
            $mergedTransactions->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $paymentMethods = Transaction::distinct()
            ->pluck('payment_method')
            ->merge(
                Booking::query()
                    ->where('user_id', Auth::id())
                    ->whereNull('booking_package_id')
                    ->pluck('payment_method')
                    ->map(fn ($method) => $method ?: 'Đặt lẻ')
            )
            ->filter()
            ->unique()
            ->values();

        return view('transactions.index', compact('transactions', 'paymentMethods'));
    }

    private function statusFilterValues(string $status): array
    {
        return $status === 'refunded'
            ? ['refunded']
            : ['success', 'paid', 'completed'];
    }

    /**
     * Hiển thị chi tiết giao dịch nếu người dùng sở hữu hoặc là admin.
     */
    public function show(Transaction $transaction): View
    {
        Gate::authorize('view', $transaction);

        $transaction->load(['booking.court.venue', 'bookingPackage.venue', 'bookingPackage.package', 'user']);

        return view('transactions.show', compact('transaction'));
    }

    private function makeVirtualBookingTransaction(Booking $booking): Transaction
    {
        $paymentStatus = match ($booking->payment_status) {
            'paid' => 'success',
            'failed' => 'failed',
            'refunded' => 'refunded',
            default => 'pending',
        };

        $transaction = new Transaction([
            'booking_id' => $booking->id,
            'booking_package_id' => null,
            'user_id' => $booking->user_id,
            'transaction_code' => 'BOOKING-' . $booking->id,
            'amount' => $booking->total_price,
            'payment_method' => $booking->payment_method ?: 'Đặt lẻ',
            'payment_gateway' => $booking->payment_method === 'vnpay' ? 'VNPay' : null,
            'payment_status' => $paymentStatus,
            'transaction_time' => $booking->updated_at ?? $booking->created_at,
            'note' => 'Giao dịch đặt sân lẻ được hiển thị từ dữ liệu booking.',
        ]);

        $transaction->exists = false;
        $transaction->created_at = $booking->created_at;
        $transaction->updated_at = $booking->updated_at;
        $transaction->setRelation('booking', $booking);
        $transaction->setRelation('bookingPackage', null);
        $transaction->setRelation('user', $booking->user);

        return $transaction;
    }
}
