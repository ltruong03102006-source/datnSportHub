@extends('admin.layouts.app')

@section('content')

<style>
    .legal-container{
        max-width:1200px;
        margin:auto;
    }

    .page-title{
        font-size:28px;
        font-weight:700;
        margin-bottom:25px;
        color:#2c3e50;
    }

    .section-card{
        background:#fff;
        border-radius:14px;
        padding:25px;
        margin-bottom:20px;
        box-shadow:0 2px 10px rgba(0,0,0,.05);
        border:1px solid #eee;
    }

    .section-title{
        font-size:18px;
        font-weight:700;
        color:#34495e;
        margin-bottom:20px;
        padding-bottom:10px;
        border-bottom:2px solid #f1f1f1;
    }

    .info-table{
        width:100%;
    }

    .info-table tr{
        border-bottom:1px solid #f3f4f6;
    }

    .info-table td{
        padding:12px 0;
    }

    .info-label{
        width:220px;
        font-weight:600;
        color:#64748b;
    }

    .info-value{
        font-weight:500;
        color:#111827;
    }

    .document-grid{
        display:grid;
        grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
        gap:20px;
    }

    .document-card{
        border:1px solid #e5e7eb;
        border-radius:12px;
        overflow:hidden;
        background:#fff;
        text-align:center;
        transition:.3s;
    }

    .document-card:hover{
        transform:translateY(-4px);
        box-shadow:0 6px 18px rgba(0,0,0,.08);
    }

    .document-card img{
        width:100%;
        height:180px;
        object-fit:cover;
        border-bottom:1px solid #eee;
    }

    .document-body{
        padding:15px;
    }

    .document-title{
        font-weight:700;
        margin-bottom:10px;
    }

    .btn-view{
        display:inline-block;
        padding:8px 14px;
        background:#2563eb;
        color:#fff;
        border-radius:8px;
        text-decoration:none;
    }

    .btn-view:hover{
        color:white;
        background:#1d4ed8;
    }

    .action-box{
        display:flex;
        justify-content:center;
        gap:15px;
        margin-top:30px;
    }

    .btn-approve{
        background:#16a34a;
        color:white;
        border:none;
        padding:12px 25px;
        border-radius:10px;
        font-weight:600;
    }

    .btn-reject{
        background:#dc2626;
        color:white;
        border:none;
        padding:12px 25px;
        border-radius:10px;
        font-weight:600;
    }

    .btn-back{
        margin-bottom:20px;
    }

</style>

<div class="legal-container">

```
<a href="{{ route('admin.venues.index') }}"
   class="btn btn-secondary btn-back">
    ← Quay lại
</a>

<h2 class="page-title">
    Hồ sơ pháp lý cơ sở
</h2>

{{-- THÔNG TIN CƠ SỞ --}}
<div class="section-card">

    <div class="section-title">
        Thông tin cơ sở
    </div>

    <table class="info-table">

        <tr>
            <td class="info-label">Tên cơ sở</td>
            <td class="info-value">
                {{ $venue->name }}
            </td>
        </tr>

        <tr>
            <td class="info-label">Địa chỉ</td>
            <td class="info-value">
                {{ $venue->address }}
            </td>
        </tr>

        <tr>
            <td class="info-label">Chủ sân</td>
            <td class="info-value">
                {{ $venue->owner->name ?? '-' }}
            </td>
        </tr>

        <tr>
            <td class="info-label">Ngày tạo</td>
            <td class="info-value">
                {{ $venue->created_at?->format('d/m/Y H:i') }}
            </td>
        </tr>

    </table>

</div>

{{-- THÔNG TIN PHÁP LÝ --}}
<div class="section-card">

    <div class="section-title">
        Thông tin pháp lý
    </div>

    <table class="info-table">

        <tr>
            <td class="info-label">Chủ sở hữu</td>
            <td class="info-value">
                {{ $venue->legalDocument?->owner_name ?? '-' }}
            </td>
        </tr>

        <tr>
            <td class="info-label">CCCD</td>
            <td class="info-value">
                {{ $venue->legalDocument?->citizen_id ?? '-' }}
            </td>
        </tr>

        <tr>
            <td class="info-label">Giấy phép kinh doanh</td>
            <td class="info-value">
                {{ $venue->legalDocument?->business_license_number ?? '-' }}
            </td>
        </tr>

        <tr>
            <td class="info-label">Ngân hàng</td>
            <td class="info-value">
                {{ $venue->legalDocument?->bank_name ?? '-' }}
            </td>
        </tr>

        <tr>
            <td class="info-label">Số tài khoản</td>
            <td class="info-value">
                {{ $venue->legalDocument?->bank_account_number ?? '-' }}
            </td>
        </tr>

    </table>

</div>


{{-- HỒ SƠ ĐÍNH KÈM --}}
<div class="section-card">

    <div class="section-title">
        Hồ sơ đính kèm
    </div>

    @if(!$venue->legalDocument)
        <div style="padding: 15px; color: #b45309; background: #fff7ed; border-radius: 8px;">
            Chưa có hồ sơ pháp lý được lưu cho cơ sở này.
        </div>
    @else
    <div class="document-grid">

        @if($venue->legalDocument?->citizen_front_image)
        <div class="document-card">

            <img src="{{ asset('storage/'.$venue->legalDocument?->citizen_front_image) }}">

            <div class="document-body">
                <div class="document-title">
                    CCCD MẶT TRƯỚC
                </div>

                <a target="_blank"
                   href="{{ asset('storage/'.$venue->legalDocument?->citizen_front_image) }}"
                   class="btn-view">
                    Xem ảnh
                </a>
            </div>

        </div>
        @endif

        @if($venue->legalDocument?->citizen_back_image)
        <div class="document-card">

            <img src="{{ asset('storage/'.$venue->legalDocument?->citizen_back_image) }}">

            <div class="document-body">
                <div class="document-title">
                    CCCD MẶT SAU
                </div>

                <a target="_blank"
                   href="{{ asset('storage/'.$venue->legalDocument?->citizen_back_image) }}"
                   class="btn-view">
                    Xem ảnh
                </a>
            </div>

        </div>
        @endif

        @if($venue->legalDocument?->business_license_file)
        <div class="document-card">

            <div class="document-body" style="padding:50px 20px">

                <i class="fa-solid fa-file-pdf"
                   style="font-size:70px;color:#dc2626"></i>

                <div class="document-title mt-3">
                    GIẤY PHÉP KINH DOANH
                </div>

                <a target="_blank"
                   href="{{ asset('storage/'.$venue->legalDocument?->business_license_file) }}"
                   class="btn-view">
                    Xem PDF
                </a>

            </div>

        </div>
        @endif

    </div>
    @endif

</div>


</div>

@endsection
