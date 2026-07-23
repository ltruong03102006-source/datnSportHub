@extends('admin.layouts.app')

@section('content')
<div style="padding: 20px;">
    
    <!-- Nút quay lại & Tiêu đề -->
    <div style="display: flex; align-items: center; margin-bottom: 24px; gap: 16px;">
        <a href="{{ route('admin.venue-transfers.index') }}" style="display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; background-color: #f1f3f5; color: #7f8c8d; border-radius: 8px; text-decoration: none; font-weight: bold;">&#8592;</a>
        <h2 style="margin: 0; color: #2c3e50; font-weight: 700;">Chi tiết Yêu cầu #{{ $transfer->id }}</h2>
        
        @if($transfer->status === 'pending')
            <span style="background-color: #fff3cd; color: #856404; padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: 600;">Chờ duyệt</span>
        @elseif($transfer->status === 'approved')
            <span style="background-color: #d4edda; color: #155724; padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: 600;">Đã duyệt</span>
        @elseif($transfer->status === 'rejected')
            <span style="background-color: #f8d7da; color: #721c24; padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: 600;">Từ chối</span>
        @endif
    </div>

    @if(session('error'))
        <div style="background-color: #f8d7da; color: #721c24; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
            {{ session('error') }}
        </div>
    @endif

    <!-- Lưới chứa thông tin -->
    <div style="display: flex; gap: 24px; align-items: flex-start;">
        
        <!-- Cột Trái: Thông tin chi tiết (Chiếm phần lớn) -->
        <div style="flex: 2; display: flex; flex-direction: column; gap: 24px;">
            
            <!-- Box: Cơ sở -->
            <div class="card-custom">
                <h3 style="margin: 0 0 16px 0; font-size: 16px; color: #2c3e50; border-bottom: 1px solid #ecf0f1; padding-bottom: 12px; text-transform: uppercase;">Thông tin Cơ sở</h3>
                <div style="display: flex; gap: 20px; align-items: center;">
                    @if($transfer->venue->banner)
                        <img src="{{ asset('storage/' . $transfer->venue->banner) }}" alt="Banner" style="width: 100px; height: 100px; border-radius: 8px; object-fit: cover; border: 1px solid #ecf0f1;">
                    @else
                        <div style="width: 100px; height: 100px; border-radius: 8px; background-color: #f1f3f5; display: flex; align-items: center; justify-content: center; color: #7f8c8d; font-size: 12px; border: 1px solid #ecf0f1;">Không ảnh</div>
                    @endif
                    <div>
                        <h4 style="margin: 0 0 8px 0; color: #2ecc71; font-size: 20px;">{{ $transfer->venue->name }}</h4>
                        <p style="margin: 0; color: #7f8c8d; font-size: 14px;">{{ $transfer->venue->address }}</p>
                    </div>
                </div>
            </div>

            <!-- Box: 2 Chủ sân (Chia 2 cột nhỏ) -->
            <div style="display: flex; gap: 24px;">
                <!-- Bên Bán -->
                <div class="card-custom" style="flex: 1; border-top: 4px solid #e74c3c;">
                    <h3 style="margin: 0 0 16px 0; font-size: 16px; color: #e74c3c; border-bottom: 1px solid #ecf0f1; padding-bottom: 12px; text-transform: uppercase;">Bên Bán (Chủ cũ)</h3>
                    <div style="display: flex; flex-direction: column; gap: 12px; font-size: 14px;">
                        <div><span style="color: #7f8c8d; display: inline-block; width: 60px;">Họ tên:</span> <strong style="color: #2c3e50;">{{ $transfer->fromOwner->name ?? $transfer->fromOwner->full_name }}</strong></div>
                        <div><span style="color: #7f8c8d; display: inline-block; width: 60px;">Email:</span> <span style="color: #2c3e50;">{{ $transfer->fromOwner->email }}</span></div>
                        <!-- CẬP NHẬT Ở ĐÂY: Lấy SĐT từ bảng venues (Cơ sở) -->
                        <div><span style="color: #7f8c8d; display: inline-block; width: 60px;">SĐT:</span> <span style="color: #2c3e50;">{{ $transfer->venue->phone ?? $transfer->fromOwner->phone ?? 'Chưa cập nhật' }}</span></div>
                    </div>
                </div>

                <!-- Bên Mua -->
                <div class="card-custom" style="flex: 1; border-top: 4px solid #2ecc71;">
                    <h3 style="margin: 0 0 16px 0; font-size: 16px; color: #2ecc71; border-bottom: 1px solid #ecf0f1; padding-bottom: 12px; text-transform: uppercase;">Bên Mua (Chủ mới)</h3>
                    <div style="display: flex; flex-direction: column; gap: 12px; font-size: 14px;">
                        <div><span style="color: #7f8c8d; display: inline-block; width: 60px;">Họ tên:</span> <strong style="color: #2c3e50;">{{ $transfer->toOwner->name ?? $transfer->toOwner->full_name }}</strong></div>
                        <div><span style="color: #7f8c8d; display: inline-block; width: 60px;">Email:</span> <span style="color: #2c3e50;">{{ $transfer->toOwner->email }}</span></div>
                        <!-- CẬP NHẬT Ở ĐÂY: Lấy SĐT từ bảng users -->
                       <div><span style="color: #7f8c8d; display: inline-block; width: 60px;">SĐT:</span> <span style="color: #2c3e50;">{{ $transfer->toOwner->phone ?? \App\Models\OwnerRegistration::where('user_id', $transfer->to_owner_id)->value('phone') ?? 'Chưa cập nhật' }}</span></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cột Phải: Quyết định (Nhỏ hơn) -->
        <div class="card-custom" style="flex: 1; position: sticky; top: 90px;">
            <h3 style="margin: 0 0 16px 0; font-size: 16px; color: #2c3e50; border-bottom: 1px solid #ecf0f1; padding-bottom: 12px; text-transform: uppercase;">Xử lý Yêu cầu</h3>
            
            <div style="margin-bottom: 24px; font-size: 13px; color: #7f8c8d;">
                <div style="margin-bottom: 8px;">Ngày tạo: <strong style="color: #2c3e50;">{{ $transfer->created_at->format('d/m/Y H:i') }}</strong></div>
                @if($transfer->status !== 'pending')
                    <div>Cập nhật: <strong style="color: #2c3e50;">{{ $transfer->updated_at->format('d/m/Y H:i') }}</strong></div>
                @endif
            </div>

            @if($transfer->status === 'rejected' && $transfer->admin_note)
                <div style="background-color: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 13px;">
                    <strong style="display: block; margin-bottom: 4px;">Lý do từ chối:</strong>
                    {{ $transfer->admin_note }}
                </div>
            @endif

            @if($transfer->status === 'pending')
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <form action="{{ route('admin.venue-transfers.approve', $transfer->id) }}" method="POST" onsubmit="return confirm('Xác nhận Duyệt? Quyền sở hữu sân sẽ thay đổi ngay lập tức!');">
                        @csrf
                        <button type="submit" style="width: 100%; padding: 12px; background-color: #2ecc71; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.backgroundColor='#27ae60'" onmouseout="this.style.backgroundColor='#2ecc71'">
                            PHÊ DUYỆT CHUYỂN NHƯỢNG
                        </button>
                    </form>
                    
                    <button type="button" onclick="document.getElementById('rejectModal').style.display='flex'" style="width: 100%; padding: 12px; background-color: transparent; color: #e74c3c; border: 1px solid #e74c3c; border-radius: 8px; font-weight: bold; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.backgroundColor='#fdf0ed'" onmouseout="this.style.backgroundColor='transparent'">
                        TỪ CHỐI
                    </button>
                </div>
            @else
                <div style="background-color: #f1f3f5; color: #7f8c8d; padding: 12px; text-align: center; border-radius: 8px; font-weight: bold; font-size: 14px;">
                    Yêu cầu đã được xử lý xong
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Từ chối -->
<div id="rejectModal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center;">
    <div style="background-color: white; width: 100%; max-width: 500px; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <form action="{{ route('admin.venue-transfers.reject', $transfer->id) }}" method="POST">
            @csrf
            <div style="padding: 20px; border-bottom: 1px solid #ecf0f1;">
                <h4 style="margin: 0; color: #e74c3c; font-weight: bold;">Từ chối yêu cầu chuyển nhượng</h4>
            </div>
            <div style="padding: 20px;">
                <label style="display: block; font-weight: 600; color: #2c3e50; margin-bottom: 8px; font-size: 14px;">Lý do từ chối (Gửi cho chủ sân):</label>
                <textarea name="admin_note" rows="4" required style="width: 100%; padding: 12px; border: 1px solid #bdc3c7; border-radius: 8px; resize: vertical; font-family: inherit; font-size: 14px; outline: none;" placeholder="Nhập lý do chi tiết vào đây..."></textarea>
            </div>
            <div style="padding: 16px 20px; background-color: #f8f9fa; border-top: 1px solid #ecf0f1; display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" onclick="document.getElementById('rejectModal').style.display='none'" style="padding: 10px 20px; background-color: white; border: 1px solid #bdc3c7; color: #2c3e50; border-radius: 6px; font-weight: 600; cursor: pointer;">Hủy</button>
                <button type="submit" style="padding: 10px 20px; background-color: #e74c3c; border: none; color: white; border-radius: 6px; font-weight: 600; cursor: pointer;">Xác nhận Từ chối</button>
            </div>
        </form>
    </div>
</div>
@endsection