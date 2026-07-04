<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Transaction;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminTransactionController extends Controller
{
    private const VISIBLE_PAYMENT_STATUSES = ['success', 'paid', 'completed', 'refunded'];

    /**
     * Hiển thị toàn bộ lịch sử giao dịch cho admin.
     */
    public function index(Request $request): View
    {
        $transactionQuery = Transaction::query()
            ->whereIn('payment_status', self::VISIBLE_PAYMENT_STATUSES)
            ->with(['booking.court.venue', 'bookingPackage.venue', 'bookingPackage.package', 'user']);

        if ($search = $request->input('search')) {
            $transactionQuery->where(function ($q) use ($search) {
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
            $transactionQuery->whereIn('payment_status', $this->statusFilterValues($status));
        }

        if ($method = $request->input('payment_method')) {
            $transactionQuery->where('payment_method', $method);
        }

        if ($dateFrom = $request->input('date_from')) {
            $transactionQuery->whereDate('transaction_time', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $transactionQuery->whereDate('transaction_time', '<=', $dateTo);
        }

        if ($month = $request->input('month')) {
            $transactionQuery->whereMonth('transaction_time', $month);
        }

        if ($year = $request->input('year')) {
            $transactionQuery->whereYear('transaction_time', $year);
        }

        $sort = $request->input('sort', 'desc');
        $transactionsFromTable = $transactionQuery
            ->orderBy('created_at', $sort === 'asc' ? 'asc' : 'desc')
            ->get();

        $bookingQuery = Booking::query()
            ->whereNull('booking_package_id')
            ->whereIn('payment_status', ['paid', 'refunded'])
            ->whereDoesntHave('transactions')
            ->with(['court.venue', 'user']);

        if ($dateFrom = $request->input('date_from')) {
            $bookingQuery->whereDate('updated_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $bookingQuery->whereDate('updated_at', '<=', $dateTo);
        }

        if ($month = $request->input('month')) {
            $bookingQuery->whereMonth('updated_at', $month);
        }

        if ($year = $request->input('year')) {
            $bookingQuery->whereYear('updated_at', $year);
        }

        if ($status = $request->input('status')) {
            $bookingQuery->whereIn('payment_status', $status === 'refunded' ? ['refunded'] : ['paid']);
        }

        $virtualTransactions = $bookingQuery
            ->get()
            ->map(fn (Booking $booking) => $this->makeVirtualBookingTransaction($booking))
            ->filter(function (Transaction $transaction) use ($request) {
                if ($search = $request->input('search')) {
                    $needle = strtolower($search);
                    $matched = str_contains(strtolower((string) $transaction->transaction_code), $needle)
                        || str_contains(strtolower((string) $transaction->user?->name), $needle)
                        || str_contains(strtolower((string) $transaction->user?->email), $needle)
                        || str_contains((string) $transaction->booking_id, $search);

                    if (! $matched) {
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
            ->sortBy(function (Transaction $transaction) {
                return optional($transaction->created_at)->timestamp ?? 0;
            }, SORT_REGULAR, $sort !== 'asc')
            ->values();

        $perPage = 15;
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
                    ->whereNull('booking_package_id')
                    ->pluck('payment_method')
                    ->map(fn ($method) => $method ?: 'Đặt lẻ')
            )
            ->filter()
            ->unique()
            ->values();

        return view('admin.transactions.index', compact('transactions', 'paymentMethods'));
    }

    private function statusFilterValues(string $status): array
    {
        return $status === 'refunded'
            ? ['refunded']
            : ['success', 'paid', 'completed'];
    }

    /**
     * Hiển thị chi tiết giao dịch cho admin.
     */
    public function show(Transaction $transaction): View
    {
        $transaction->load(['booking.court.venue', 'bookingPackage.venue', 'bookingPackage.package', 'user']);

        return view('admin.transactions.show', compact('transaction'));
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
