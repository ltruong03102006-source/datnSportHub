@extends('admin.layouts.app')

@section('title', 'Chatbot logs')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Chatbot logs</h1>
            <p class="text-muted mb-0">Theo dõi các cuộc hội thoại hỗ trợ tự động.</p>
        </div>
        <form method="GET" class="d-flex gap-2">
            <select name="status" class="form-select">
                <option value="">Tất cả trạng thái</option>
                <option value="open" @selected(request('status') === 'open')>Open</option>
                <option value="closed" @selected(request('status') === 'closed')>Closed</option>
            </select>
            <button class="btn btn-primary">Lọc</button>
        </form>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Người dùng</th>
                        <th>Trạng thái</th>
                        <th>Tin nhắn cuối</th>
                        <th>Cập nhật</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($conversations as $conversation)
                        <tr>
                            <td class="fw-bold">#{{ $conversation->id }}</td>
                            <td>{{ $conversation->user?->name ?? 'Khách vãng lai' }}</td>
                            <td><span class="badge bg-{{ $conversation->status === 'open' ? 'success' : 'secondary' }}">{{ $conversation->status }}</span></td>
                            <td class="text-muted">{{ $conversation->messages->first()?->message ?? '-' }}</td>
                            <td>{{ $conversation->last_message_at?->format('d/m/Y H:i') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-5 text-center text-muted">Chưa có hội thoại chatbot nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $conversations->links() }}
    </div>
</div>
@endsection
