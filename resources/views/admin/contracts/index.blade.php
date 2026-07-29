@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
                        <div>
                            <h1 class="h3 mb-1">Quản lý hợp đồng kinh doanh</h1>
                            <p class="text-muted mb-0">Danh sách hợp đồng giữa Admin và Chủ sân.</p>
                        </div>
                        <div>
                            <a href="#" class="btn btn-primary">Tạo hợp đồng</a>
                        </div>
                    </div>

                    @if($contracts->isEmpty())
                        <div class="alert alert-secondary" role="alert">
                            Chưa có hợp đồng nào.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="thead-light">
                                    <tr>
                                        <th scope="col">STT</th>
                                        <th scope="col">Mã hợp đồng</th>
                                        <th scope="col">Chủ sân</th>
                                        <th scope="col">Admin tạo</th>
                                        <th scope="col">Tiêu đề</th>
                                        <th scope="col">Hoa hồng (%)</th>
                                        <th scope="col">Ngày bắt đầu</th>
                                        <th scope="col">Ngày kết thúc</th>
                                        <th scope="col">Trạng thái</th>
                                        <th scope="col">Ngày tạo</th>
                                        <th scope="col">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($contracts as $index => $contract)
                                        <tr>
                                            <td>{{ $contracts->firstItem() + $index }}</td>
                                            <td>{{ $contract->contract_code }}</td>
                                            <td>{{ $contract->owner?->name ?? '-' }}</td>
                                            <td>{{ $contract->creator?->name ?? '-' }}</td>
                                            <td>{{ $contract->title }}</td>
                                            <td>{{ number_format($contract->commission_rate, 2) }}</td>
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
                                            <td>{{ $contract->created_at->format('Y-m-d H:i') }}</td>
                                            <td>
                                                <a href="#" class="btn btn-sm btn-outline-primary me-1">Xem</a>
                                                <a href="#" class="btn btn-sm btn-outline-secondary">Sửa</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end">
                            {{ $contracts->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
