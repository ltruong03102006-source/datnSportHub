@extends('admin.layouts.app')

@section('content')
<div style="padding: 20px;">
    
    <!-- Nút quay lại & Tiêu đề -->
    <div style="display: flex; align-items: center; margin-bottom: 24px; gap: 16px;">
        <a href="{{ route('admin.venue-transfers.index') }}" style="display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; background-color: #f1f3f5; color: #7f8c8d; border-radius: 8px; text-decoration: none; font-weight: bold;">&#8592;</a>
        <h2 style="margin: 0; color: #2c3e50; font-weight: 700;">Chi tiết Yêu cầu #{{ $transfer->id }}</h2>
        
        @if($transfer->status === 'draft')
            <span style="background-color: #fff3cd; color: #856404; padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: 600;">Hợp đồng Nháp</span>
        @elseif(in_array($transfer->status, ['sent', 'pending']))
            <span style="background-color: #e2e8f0; color: #334155; padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: 600;">Chờ chủ mới nhận</span>
        @elseif($transfer->status === 'filled')
            <span style="background-color: #f3e8ff; color: #6b21a8; padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: 600;">Đã điền hồ sơ (Chờ ký)</span>
        @elseif(in_array($transfer->status, ['signed', 'pending_admin']))
            <span style="background-color: #cce5ff; color: #004085; padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: 600;">Đã ký (Chờ Admin duyệt)</span>
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
            <div class="card-custom" style="padding: 20px; background: #fff; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); border: 1px solid #ecf0f1;">
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
                <div class="card-custom" style="flex: 1; padding: 20px; background: #fff; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); border: 1px solid #ecf0f1; border-top: 4px solid #e74c3c;">
                    <h3 style="margin: 0 0 16px 0; font-size: 16px; color: #e74c3c; border-bottom: 1px solid #ecf0f1; padding-bottom: 12px; text-transform: uppercase;">Bên Bán (Chủ cũ)</h3>
                    <div style="display: flex; flex-direction: column; gap: 12px; font-size: 14px;">
                        <div><span style="color: #7f8c8d; display: inline-block; width: 110px;">Họ tên:</span> <strong style="color: #2c3e50;">{{ $transfer->sender_data['owner_name'] ?? $transfer->fromOwner->name ?? $transfer->fromOwner->full_name }}</strong></div>
                        @if(!empty($transfer->sender_data['dob']))
                            <div><span style="color: #7f8c8d; display: inline-block; width: 110px;">Ngày sinh:</span> <span style="color: #2c3e50;">{{ \Carbon\Carbon::parse($transfer->sender_data['dob'])->format('d/m/Y') }}</span></div>
                        @endif
                        @if(!empty($transfer->sender_data['address']))
                            <div><span style="color: #7f8c8d; display: inline-block; width: 110px;">Chỗ ở hiện tại:</span> <span style="color: #2c3e50;">{{ $transfer->sender_data['address'] }}</span></div>
                        @endif
                        <div><span style="color: #7f8c8d; display: inline-block; width: 110px;">Email:</span> <span style="color: #2c3e50;">{{ $transfer->fromOwner->email }}</span></div>
                        <div><span style="color: #7f8c8d; display: inline-block; width: 110px;">SĐT:</span> <span style="color: #2c3e50;">{{ $transfer->venue->phone ?? $transfer->fromOwner->phone ?? 'Chưa cập nhật' }}</span></div>
                        @if($transfer->sender_signed_at)
                            <div style="margin-top: 8px; padding: 8px; background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; font-size: 12px;">
                                <div style="color: #166534; font-weight: bold; margin-bottom: 4px;">✓ Chữ ký số xác nhận khởi tạo</div>
                                <div style="color: #374151;">Thời gian ký: {{ \Carbon\Carbon::parse($transfer->sender_signed_at)->format('H:i:s - d/m/Y') }}</div>
                                <div style="color: #374151;">Tài khoản: {{ $transfer->sender_signed_account ?? $transfer->fromOwner->email }}</div>
                                <div style="color: #374151;">IP xác thực: {{ $transfer->sender_signed_ip ?? '127.0.0.1' }}</div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Bên Mua -->
                <div class="card-custom" style="flex: 1; padding: 20px; background: #fff; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); border: 1px solid #ecf0f1; border-top: 4px solid #2ecc71;">
                    <h3 style="margin: 0 0 16px 0; font-size: 16px; color: #2ecc71; border-bottom: 1px solid #ecf0f1; padding-bottom: 12px; text-transform: uppercase;">Bên Mua (Chủ mới)</h3>
                    <div style="display: flex; flex-direction: column; gap: 12px; font-size: 14px;">
                        <div><span style="color: #7f8c8d; display: inline-block; width: 110px;">Họ tên:</span> <strong style="color: #2c3e50;">{{ $transfer->receiver_data['owner_name'] ?? $transfer->toOwner->name ?? $transfer->toOwner->full_name }}</strong></div>
                        @if(!empty($transfer->receiver_data['dob']))
                            <div><span style="color: #7f8c8d; display: inline-block; width: 110px;">Ngày sinh:</span> <span style="color: #2c3e50;">{{ \Carbon\Carbon::parse($transfer->receiver_data['dob'])->format('d/m/Y') }}</span></div>
                        @endif
                        @if(!empty($transfer->receiver_data['address']))
                            <div><span style="color: #7f8c8d; display: inline-block; width: 110px;">Chỗ ở hiện tại:</span> <span style="color: #2c3e50;">{{ $transfer->receiver_data['address'] }}</span></div>
                        @endif
                        <div><span style="color: #7f8c8d; display: inline-block; width: 110px;">Email:</span> <span style="color: #2c3e50;">{{ $transfer->toOwner->email }}</span></div>
                        <div><span style="color: #7f8c8d; display: inline-block; width: 110px;">SĐT:</span> <span style="color: #2c3e50;">{{ $transfer->toOwner->phone ?? \App\Models\OwnerRegistration::where('user_id', $transfer->to_owner_id)->value('phone') ?? 'Chưa cập nhật' }}</span></div>
                        @if($transfer->receiver_signed_at)
                            <div style="margin-top: 8px; padding: 8px; background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; font-size: 12px;">
                                <div style="color: #166534; font-weight: bold; margin-bottom: 4px;">✓ Chữ ký số điện tử xác thực</div>
                                <div style="color: #374151;">Thời gian ký: {{ \Carbon\Carbon::parse($transfer->receiver_signed_at)->format('H:i:s - d/m/Y') }}</div>
                                <div style="color: #374151;">Tài khoản: {{ $transfer->receiver_signed_account ?? $transfer->toOwner->email }}</div>
                                <div style="color: #374151;">IP xác thực: {{ $transfer->receiver_signed_ip ?? '127.0.0.1' }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Box: HỒ SƠ PHÁP LÝ (CHỈ HIỆN KHI CHỦ MỚI ĐÃ NỘP HOẶC ĐÃ DUYỆT) -->
            @if(in_array($transfer->status, ['pending_admin', 'approved']) && is_array($transfer->receiver_data))
            <div class="card-custom" style="padding: 20px; background: #fff; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); border: 1px solid #ecf0f1; border-top: 4px solid #3498db;">
                <h3 style="margin: 0 0 16px 0; font-size: 16px; color: #3498db; border-bottom: 1px solid #ecf0f1; padding-bottom: 12px; text-transform: uppercase;">Hồ sơ pháp lý(Chủ mới nộp)</h3>
                
<div style="display: flex; gap: 24px;">
    <!-- Cột 1: Thông tin liên hệ & Pháp lý -->
    <div style="flex: 1; display: flex; flex-direction: column; gap: 12px; font-size: 14px;">
        <div><span style="color: #7f8c8d; display: inline-block; width: 120px;">Tên pháp lý:</span> <strong style="color: #2c3e50;">{{ $transfer->receiver_data['owner_name'] ?? '' }}</strong></div>
        @if(!empty($transfer->receiver_data['dob']))
            <div><span style="color: #7f8c8d; display: inline-block; width: 120px;">Ngày sinh:</span> <strong style="color: #2c3e50;">{{ \Carbon\Carbon::parse($transfer->receiver_data['dob'])->format('d/m/Y') }}</strong></div>
        @endif
        @if(!empty($transfer->receiver_data['address']))
            <div><span style="color: #7f8c8d; display: inline-block; width: 120px;">Chỗ ở hiện tại:</span> <strong style="color: #2c3e50;">{{ $transfer->receiver_data['address'] }}</strong></div>
        @endif
        <div><span style="color: #7f8c8d; display: inline-block; width: 120px;">Số CCCD:</span> <strong style="color: #2c3e50;">{{ $transfer->receiver_data['citizen_id'] ?? '' }}</strong></div>
        <div><span style="color: #7f8c8d; display: inline-block; width: 120px;">Mã số GPKD:</span> <strong style="color: #2c3e50;">{{ $transfer->receiver_data['business_license_number'] ?? 'Không có' }}</strong></div>
        
        <!-- ĐÃ THÊM: Email và SĐT sân mới -->
        <div style="margin-top: 8px; padding-top: 8px; border-top: 1px dashed #bdc3c7;">
            <span style="color: #7f8c8d; display: inline-block; width: 120px;">SĐT Sân mới:</span> <strong style="color: #e67e22;">{{ $transfer->receiver_data['phone'] ?? 'Không có' }}</strong>
        </div>
        <div>
            <span style="color: #7f8c8d; display: inline-block; width: 120px;">Email Sân mới:</span> <strong style="color: #e67e22;">{{ $transfer->receiver_data['email'] ?? 'Không có' }}</strong>
        </div>
    </div>
    
    <!-- Cột 2: Ngân hàng -->
    <div style="flex: 1; display: flex; flex-direction: column; gap: 12px; font-size: 14px;">
        <div><span style="color: #7f8c8d; display: inline-block; width: 100px;">Ngân hàng:</span> <strong style="color: #2c3e50;">{{ $transfer->receiver_data['bank_name'] ?? '' }}</strong></div>
        <div><span style="color: #7f8c8d; display: inline-block; width: 100px;">Số tài khoản:</span> <strong style="color: #2c3e50;">{{ $transfer->receiver_data['bank_account_number'] ?? '' }}</strong></div>
        <div><span style="color: #7f8c8d; display: inline-block; width: 100px;">Chủ tài khoản:</span> <strong style="color: #2c3e50;">{{ $transfer->receiver_data['bank_account_holder'] ?? '' }}</strong></div>
    </div>
</div>

<!-- ... code phần Hiển thị nút bấm Đính kèm giữ nguyên ... -->

                <h4 style="margin: 20px 0 12px 0; font-size: 14px; color: #2c3e50;">Tài liệu đính kèm (Nhấn vào để xem):</h4>
                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    @if(isset($transfer->receiver_data['citizen_front_image']))
                        <a href="{{ Storage::url($transfer->receiver_data['citizen_front_image']) }}" target="_blank" style="padding: 6px 12px; background-color: #f0f9ff; color: #0284c7; border: 1px solid #bae6fd; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 600;">Ảnh CCCD Mặt trước</a>
                    @endif
                    
                    @if(isset($transfer->receiver_data['citizen_back_image']))
                        <a href="{{ Storage::url($transfer->receiver_data['citizen_back_image']) }}" target="_blank" style="padding: 6px 12px; background-color: #f0f9ff; color: #0284c7; border: 1px solid #bae6fd; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 600;">Ảnh CCCD Mặt sau</a>
                    @endif

                    @if(isset($transfer->receiver_data['business_license_file']))
                        <a href="{{ Storage::url($transfer->receiver_data['business_license_file']) }}" target="_blank" style="padding: 6px 12px; background-color: #f0f9ff; color: #0284c7; border: 1px solid #bae6fd; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 600;">Giấy phép KD</a>
                    @endif

                    @if(isset($transfer->receiver_data['rental_contract_file']))
                        <a href="{{ Storage::url($transfer->receiver_data['rental_contract_file']) }}" target="_blank" style="padding: 6px 12px; background-color: #f0f9ff; color: #0284c7; border: 1px solid #bae6fd; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 600;">Hợp đồng thuê</a>
                    @endif

                    @if(isset($transfer->receiver_data['land_certificate_file']))
                        <a href="{{ Storage::url($transfer->receiver_data['land_certificate_file']) }}" target="_blank" style="padding: 6px 12px; background-color: #f0f9ff; color: #0284c7; border: 1px solid #bae6fd; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 600;">Sổ đỏ/Sổ hồng</a>
                    @endif
                </div>
            </div>
            @endif

        </div>

        <!-- Cột Phải: Quyết định (Nhỏ hơn) -->
        <div class="card-custom" style="flex: 1; position: sticky; top: 90px; padding: 20px; background: #fff; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); border: 1px solid #ecf0f1;">
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

            <!-- LOGIC NÚT DUYỆT ĐÃ ĐƯỢC CHUẨN HÓA -->
            @if(in_array($transfer->status, ['draft', 'sent', 'pending', 'filled']))
                <div style="background-color: #fff3cd; color: #856404; padding: 12px; text-align: center; border-radius: 8px; font-size: 13px;">
                    <i class="fa-solid fa-hourglass-half"></i> Đang chờ các bên hoàn tất điền hồ sơ và ký điện tử... Chưa thể duyệt lúc này.
                </div>
            @elseif(in_array($transfer->status, ['signed', 'pending_admin']))
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <form action="{{ route('admin.venue-transfers.approve', $transfer->id) }}" method="POST" onsubmit="return confirm('Xác nhận Duyệt? Quyền sở hữu sân và dòng tiền sẽ thay đổi ngay lập tức!');">
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

<!-- Modal Từ chối (Giữ nguyên của bạn) -->
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