<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hợp đồng của tôi - Chủ sân</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .page-header { background: #fff; border: 1px solid #e2e8f0; border-radius: 1rem; box-shadow: 0 10px 30px rgba(15, 23, 42, .05); }
        .content-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 1rem; box-shadow: 0 10px 30px rgba(15, 23, 42, .04); }
    </style>
</head>
<body class="text-slate-800 antialiased min-h-screen">
    <nav class="bg-white shadow-sm border-bottom border-slate-200 px-4 py-3 d-flex justify-content-between align-items-center">
        <div>
            <a href="{{ route('owner.dashboard') }}" class="text-xl fw-bold text-slate-800">SportHub</a>
        </div>
        <div class="d-flex align-items-center gap-3 small text-secondary">
            <a href="{{ route('owner.dashboard') }}" class="text-decoration-none text-secondary">Dashboard</a>
            <a href="{{ route('owner.venues.index') }}" class="text-decoration-none text-secondary">Cơ sở</a>
            <a href="{{ route('owner.contracts.index') }}" class="text-decoration-none text-success fw-semibold">Hợp đồng</a>
        </div>
    </nav>

    <main class="container py-5">
        <div class="page-header p-4 mb-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="h3 mb-1">Hợp đồng của tôi</h1>
                    <p class="text-muted mb-0">Xem danh sách hợp đồng dành cho Chủ sân.</p>
                </div>
            </div>
        </div>

        <div class="content-card p-4">
            @if($contracts->isEmpty())
                <div class="py-5 text-center text-secondary">
                    <h2 class="h5 mb-2">Bạn chưa có hợp đồng nào.</h2>
                    <p class="mb-0">Khi hợp đồng được gửi, bạn sẽ thấy danh sách chi tiết ở đây.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-borderless align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">STT</th>
                                <th scope="col">Mã</th>
                                <th scope="col">Tiêu đề</th>
                                <th scope="col">Hoa hồng</th>
                                <th scope="col">Bắt đầu</th>
                                <th scope="col">Kết thúc</th>
                                <th scope="col">Trạng thái</th>
                                <th scope="col">Ngày tạo</th>
                                <th scope="col">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($contracts as $index => $contract)
                                <tr class="border-top">
                                    <td>{{ $contracts->firstItem() + $index }}</td>
                                    <td class="fw-semibold">{{ $contract->contract_code }}</td>
                                    <td>{{ $contract->title }}</td>
                                    <td>{{ number_format($contract->commission_rate, 2) }}%</td>
                                    <td>{{ $contract->start_date?->format('Y-m-d') }}</td>
                                    <td>{{ $contract->end_date?->format('Y-m-d') }}</td>
                                    <td>
                                        @php
                                            $badge = match($contract->status) {
                                                'draft' => 'secondary',
                                                'sent' => 'primary',
                                                'accepted' => 'success',
                                                'rejected' => 'danger',
                                                'expired' => 'warning',
                                                'terminated' => 'dark',
                                                default => 'secondary',
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $badge }} text-capitalize">{{ $contract->status }}</span>
                                    </td>
                                    <td>{{ $contract->created_at->format('Y-m-d') }}</td>
                                    <td>
                                        <a href="{{ route('owner.contracts.show', $contract) }}" class="btn btn-sm btn-outline-success">Xem chi tiết</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $contracts->links() }}
                </div>
            @endif
        </div>
    </main>
</body>
</html>
