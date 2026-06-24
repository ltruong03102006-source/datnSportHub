@extends('layouts.app')

@section('title', 'Tìm Sân Gần Đây | SportHub')

@section('styles')
    {{-- Leaflet.js Map CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    {{-- Leaflet.markercluster CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css"/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css"/>
    {{-- Google Fonts - Inter & Be Vietnam Pro --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        /* ==========================================================================
           NEARBY VENUES - PREMIUM DESIGN SYSTEM & CUSTOM STYLES
           ========================================================================== */

        /* 1. Global Reset & Overrides */
        html, body {
            overflow: hidden !important;
            height: 100vh !important;
            margin: 0;
            padding: 0;
            background-color: #F8FAFC;
        }

        /* Hide global footer of layout to prevent double footer scroll */
        footer {
            display: none !important;
        }

        /* Main container fits exactly below navbar (h-16 = 64px) */
        main.flex-1 {
            height: calc(100vh - 64px) !important;
            max-height: calc(100vh - 64px) !important;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            padding: 0 !important;
            margin: 0 !important;
        }

        /* Root Layout - 32% Sidebar / 68% Map */
        .nearby-root {
            display: flex;
            flex: 1;
            height: 100%;
            overflow: hidden;
            font-family: 'Inter', 'Be Vietnam Pro', sans-serif !important;
        }

        /* ---------- SIDEBAR (32%) ---------- */
        .nearby-sidebar {
            width: 32%;
            min-width: 400px;
            max-width: 480px;
            display: flex;
            flex-direction: column;
            background: #ffffff;
            border-right: 1px solid #E5E7EB;
            z-index: 20;
            box-shadow: 4px 0 24px rgba(15, 23, 42, 0.02);
            transition: all 0.3s ease;
        }

        .nearby-sidebar-header {
            padding: 24px;
            border-bottom: 1px solid #F1F5F9;
            background: #ffffff;
            flex-shrink: 0;
        }

        .nearby-sidebar-list {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #F8FAFC;
        }

        /* Custom Scrollbar */
        .nearby-sidebar-list::-webkit-scrollbar {
            width: 6px;
        }
        .nearby-sidebar-list::-webkit-scrollbar-track {
            background: transparent;
        }
        .nearby-sidebar-list::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 99px;
        }
        .nearby-sidebar-list::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* ---------- MAP (68%) ---------- */
        .nearby-map-wrap {
            flex: 1;
            position: relative;
            height: 100%;
        }

        #map {
            width: 100%;
            height: 100%;
        }

        /* Fix Leaflet Z-Index relative to Navbar dropdowns */
        .leaflet-container {
            z-index: 10 !important;
        }
        .leaflet-top, .leaflet-bottom {
            z-index: 10 !important;
        }

        /* ---------- AIRBNB STYLE CARDS ---------- */
        .vcard {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #E5E7EB;
            margin-bottom: 16px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 4px rgba(15, 23, 42, 0.015);
            position: relative;
        }
        .vcard:hover {
            transform: translateY(-4px);
            border-color: #10B981;
            box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.08), 0 8px 10px -6px rgba(16, 185, 129, 0.05);
        }
        .vcard.active {
            border-color: #10B981;
            background: rgba(16, 185, 129, 0.01);
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.15), 0 8px 20px rgba(16, 185, 129, 0.06);
        }

        /* Custom marker styling (Bubble Rating) */
        .custom-map-marker-container {
            background: transparent;
            border: none;
        }
        .custom-marker-bubble {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            background: #ffffff;
            border: 1.5px solid #E5E7EB;
            border-radius: 9999px;
            padding: 4px 8px;
            font-size: 11px;
            font-weight: 800;
            color: #1F2937;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            position: relative;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .custom-marker-bubble::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%);
            border-width: 5px 5px 0;
            border-style: solid;
            border-color: #ffffff transparent transparent;
            display: block;
            width: 0;
        }
        .custom-marker-bubble:hover, .custom-marker-bubble.active-highlight {
            background: #10B981 !important;
            border-color: #10B981 !important;
            color: #ffffff !important;
            transform: scale(1.1) translateY(-2px);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.35);
            z-index: 999;
        }
        .custom-marker-bubble:hover::after, .custom-marker-bubble.active-highlight::after {
            border-color: #10B981 transparent transparent !important;
        }

        /* Pulsing user marker override */
        .user-pin {
            background: #3B82F6;
            border-radius: 50%;
            border: 3px solid #ffffff;
            box-shadow: 0 0 12px rgba(59, 130, 246, 0.5);
            animation: pulse-ring 1.8s infinite;
        }
        @keyframes pulse-ring {
            0%   { box-shadow: 0 0 0 0px rgba(59, 130, 246, 0.7); }
            70%  { box-shadow: 0 0 0 12px rgba(59, 130, 246, 0); }
            100% { box-shadow: 0 0 0 0px rgba(59, 130, 246, 0); }
        }

        /* Hide scrollbars for sliders */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* ---------- MOBILE RESPONSIVE (Breakpoint < 768px) ---------- */
        @media (max-width: 768px) {
            html, body {
                overflow: hidden !important;
                height: 100vh !important;
            }
            main.flex-1 {
                height: calc(100vh - 72px) !important;
                max-height: calc(100vh - 72px) !important;
                overflow: hidden !important;
                position: relative !important;
            }
            .nearby-root {
                flex-direction: column;
                height: 100% !important;
                position: relative;
            }
            /* Hide desktop sidebar */
            .nearby-sidebar {
                display: none !important;
            }
            /* Map occupies fullscreen */
            .nearby-map-wrap {
                width: 100% !important;
                height: 100% !important;
                position: absolute;
                inset: 0;
                z-index: 10;
            }
        }
    </style>
@endsection

@section('content')
<div class="nearby-root">

    {{-- ==========================================================================
         SIDEBAR: Filters, Inputs, Results List (Desktop Only)
         ========================================================================== --}}
    <aside class="nearby-sidebar">
        
        {{-- Sidebar Header Controls --}}
        <div class="nearby-sidebar-header">
            <!-- Title -->
            <div class="flex items-center gap-3 mb-6">
                <div class="grid place-items-center w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="w-5.5 h-5.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg font-black text-zinc-900 tracking-tight">Tìm Sân Gần Đây</h1>
                    <p class="text-xs font-semibold text-zinc-400">Khám phá các câu lạc bộ quanh bạn</p>
                </div>
            </div>

            {{-- 📍 Location Status Card --}}
            <div class="bg-slate-50 border border-zinc-100 rounded-2xl p-4 mb-4">
                <div class="flex items-center justify-between mb-2.5">
                    <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider">📍 Vị trí định vị</span>
                    <span id="gps-status-badge" class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-full bg-zinc-200/60 text-zinc-500">Chưa định vị</span>
                </div>
                <button id="gps-btn" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-zinc-200 text-zinc-800 text-sm font-bold shadow-sm hover:bg-slate-50 hover:border-zinc-300 active:scale-[0.98] transition-all duration-200">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                    </svg>
                    <span id="gps-label">Định vị vị trí của tôi</span>
                </button>
            </div>

            {{-- 🔍 Search Input --}}
            <div class="relative flex items-center gap-2 mb-4">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <svg class="w-4.5 h-4.5 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </span>
                    <input id="keyword-search" type="text"
                           placeholder="Nhập tên sân hoặc địa chỉ..."
                           class="w-full rounded-xl border border-zinc-200 bg-white pl-10 pr-4 py-2.5 text-sm font-semibold text-zinc-800 placeholder-zinc-400 shadow-sm hover:border-zinc-300 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all duration-200">
                </div>
                <button id="search-btn"
                        class="rounded-xl bg-emerald-500 hover:bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-md hover:shadow-emerald-500/25 active:scale-95 transition-all duration-200">
                    Tìm
                </button>
            </div>

            {{-- Filter Chips: Sports --}}
            <div class="mb-4">
                <label class="block text-[10px] font-bold text-zinc-400 uppercase tracking-wider mb-2">Bộ môn thể thao</label>
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 no-scrollbar scroll-smooth">
                    <button type="button" class="sport-chip shrink-0 px-3.5 py-1.5 rounded-full text-xs font-bold border transition-all duration-200 bg-emerald-500 border-emerald-500 text-white shadow-sm" data-id="all">
                        🎾 Tất cả môn
                    </button>
                    @foreach($sports as $sport)
                        @php
                            $emoji = '⚽';
                            if (str_contains(strtolower($sport->slug), 'bong-da')) $emoji = '⚽';
                            elseif (str_contains(strtolower($sport->slug), 'bong-ban')) $emoji = '🏓';
                            elseif (str_contains(strtolower($sport->slug), 'pickleball')) $emoji = '🏓';
                            elseif (str_contains(strtolower($sport->slug), 'cau-long')) $emoji = '🏸';
                            elseif (str_contains(strtolower($sport->slug), 'tennis')) $emoji = '🎾';
                            elseif (str_contains(strtolower($sport->slug), 'bong-ro')) $emoji = '🏀';
                        @endphp
                        <button type="button" class="sport-chip shrink-0 px-3.5 py-1.5 rounded-full text-xs font-bold border border-zinc-200 transition-all duration-200 bg-white text-zinc-700 hover:bg-slate-50" data-id="{{ $sport->id }}">
                            {{ $emoji }} {{ $sport->name }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Filter Chips: Distance --}}
            <div>
                <label class="block text-[10px] font-bold text-zinc-400 uppercase tracking-wider mb-2">Bán kính tìm kiếm</label>
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 no-scrollbar">
                    <button type="button" class="dist-chip shrink-0 px-3.5 py-1.5 rounded-full text-xs font-bold border border-zinc-200 transition-all duration-200 bg-white text-zinc-700 hover:bg-slate-50" data-radius="1">1 km</button>
                    <button type="button" class="dist-chip shrink-0 px-3.5 py-1.5 rounded-full text-xs font-bold border border-zinc-200 transition-all duration-200 bg-white text-zinc-700 hover:bg-slate-50" data-radius="3">3 km</button>
                    <button type="button" class="dist-chip shrink-0 px-3.5 py-1.5 rounded-full text-xs font-bold border border-emerald-500 bg-emerald-500 text-white shadow-sm" data-radius="5">5 km</button>
                    <button type="button" class="dist-chip shrink-0 px-3.5 py-1.5 rounded-full text-xs font-bold border border-zinc-200 transition-all duration-200 bg-white text-zinc-700 hover:bg-slate-50" data-radius="10">10 km</button>
                    <button type="button" class="dist-chip shrink-0 px-3.5 py-1.5 rounded-full text-xs font-bold border border-zinc-200 transition-all duration-200 bg-white text-zinc-700 hover:bg-slate-50" data-radius="all">Tất cả</button>
                </div>
            </div>

        </div>

        {{-- Scrollable list --}}
        <div id="venue-list" class="nearby-sidebar-list">
            
            {{-- Results counter --}}
            <div class="flex items-center justify-between pb-3.5 mb-2 border-b border-zinc-200/50">
                <span id="results-count" class="text-xs font-extrabold text-zinc-800 uppercase tracking-wider">Đang quét vị trí...</span>
                <span class="text-[10px] text-zinc-400 font-bold uppercase tracking-wider">Cập nhật thực tế</span>
            </div>

            <!-- Sort and Filter Quick Actions -->
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-zinc-100 text-xs gap-2">
                <!-- Sort option chips -->
                <div class="flex items-center gap-1.5" id="sort-container">
                    <button type="button" class="sort-chip px-2.5 py-1 rounded bg-zinc-950 text-white font-bold transition-all shadow-sm" data-sort="distance">📍 Gần nhất</button>
                    <button type="button" class="sort-chip px-2.5 py-1 rounded bg-white border border-zinc-200 text-zinc-600 font-semibold hover:bg-slate-50 transition-all" data-sort="rating">⭐ Đánh giá</button>
                </div>
                
                <!-- Open only toggle -->
                <label class="flex items-center gap-1.5 cursor-pointer select-none text-zinc-600 font-semibold">
                    <input type="checkbox" id="open-only-toggle" class="rounded text-emerald-500 focus:ring-emerald-500/20 w-3.5 h-3.5 border-zinc-300">
                    <span>Đang mở cửa</span>
                </label>
            </div>

            {{-- Skeleton loading state --}}
            <div id="state-loading" class="space-y-4">
                @for ($i = 0; $i < 3; $i++)
                    <div class="animate-pulse flex h-[150px] rounded-2xl border border-zinc-200 bg-white overflow-hidden shadow-sm">
                        <div class="w-[130px] bg-zinc-200 h-full shrink-0"></div>
                        <div class="flex-1 p-3.5 flex flex-col justify-between">
                            <div>
                                <div class="flex items-start justify-between mb-2">
                                    <div class="h-4 bg-zinc-200 rounded w-2/3"></div>
                                    <div class="h-4 bg-zinc-200 rounded w-10"></div>
                                </div>
                                <div class="h-3 bg-zinc-200 rounded w-5/6 mb-2"></div>
                                <div class="h-3 bg-zinc-200 rounded w-1/2"></div>
                            </div>
                            <div class="flex items-center justify-between border-t border-zinc-100 pt-2">
                                <div class="h-3 bg-zinc-200 rounded w-1/3"></div>
                                <div class="h-7 bg-zinc-200 rounded w-16"></div>
                            </div>
                        </div>
                    </div>
                @endfor
            </div>

            {{-- Cards Container --}}
            <div id="cards-container" class="hidden space-y-4"></div>

            {{-- Empty State template --}}
            <div id="state-empty" class="hidden flex-col items-center justify-center p-8 text-center bg-white border border-zinc-200 border-dashed rounded-2xl">
                <div class="w-16 h-16 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Z"/>
                    </svg>
                </div>
                <h3 class="text-base font-extrabold text-zinc-900 mb-1">Không tìm thấy sân phù hợp</h3>
                <p class="text-xs text-zinc-500 mb-4 leading-relaxed">Hãy thử áp dụng một số gợi ý dưới đây để mở rộng phạm vi tìm kiếm của bạn:</p>
                <div class="text-left w-full space-y-2 bg-slate-50 p-3.5 rounded-xl border border-zinc-100 text-xs font-semibold text-zinc-600 mb-4">
                    <div class="flex items-start gap-2">
                        <span class="text-emerald-500">•</span>
                        <span>Tăng bán kính tìm kiếm lên 10km hoặc chọn "Tất cả".</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-emerald-500">•</span>
                        <span>Thay đổi bộ môn thể thao khác hoặc chọn "Tất cả môn".</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-emerald-500">•</span>
                        <span>Xóa từ khóa tìm kiếm trong ô nhập liệu.</span>
                    </div>
                </div>
                <button onclick="resetFilters()" class="w-full py-2.5 bg-zinc-950 text-white text-xs font-bold rounded-xl hover:bg-emerald-600 active:scale-95 transition-all duration-200">
                    Xóa tất cả bộ lọc
                </button>
            </div>

            {{-- 🖤 Premium Dark Footer --}}
            <footer class="mt-8 border-t border-zinc-800 bg-zinc-950 p-6 rounded-2xl text-zinc-400">
                <div class="flex items-center gap-2 mb-4">
                    <span class="grid h-7 w-7 place-items-center rounded-lg bg-emerald-500 text-xs font-black text-white">S</span>
                    <span class="text-sm font-bold text-white tracking-tight">SportHub</span>
                </div>
                <p class="text-[11px] leading-relaxed text-zinc-500 mb-4">
                    Nền tảng tìm kiếm và đặt sân thể thao hiện đại, mang lại trải nghiệm tiện lợi và nhanh chóng nhất.
                </p>
                <div class="grid grid-cols-2 gap-4 text-xs font-semibold mb-4">
                    <div class="space-y-2">
                        <a href="#" class="block hover:text-white transition-colors duration-200">Điều khoản</a>
                        <a href="#" class="block hover:text-white transition-colors duration-200">Chính sách bảo mật</a>
                    </div>
                    <div class="space-y-2">
                        <a href="#" class="block hover:text-white transition-colors duration-200">Liên hệ hỗ trợ</a>
                        <a href="tel:19001234" class="block hover:text-white transition-colors duration-200">Hotline: 1900 1234</a>
                    </div>
                </div>
                <div class="flex justify-between items-center border-t border-zinc-900 pt-4 text-[10px] text-zinc-600">
                    <span>© {{ date('Y') }} SportHub.</span>
                    <div class="flex gap-3 text-zinc-500">
                        <a href="#" class="hover:text-white"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/></svg></a>
                    </div>
                </div>
            </footer>

        </div>
    </aside>

    {{-- ==========================================================================
         MAP VIEWPORT (Occupies right side on desktop, full screen on mobile)
         ========================================================================== --}}
    <div class="nearby-map-wrap">
        <div id="map"></div>

        {{-- 🗺️ Floating "Search in this area" button --}}
        <button id="search-here-btn" class="hidden absolute top-4 left-1/2 -translate-x-1/2 z-20 flex items-center gap-1.5 px-4 py-2.5 rounded-full bg-white border border-zinc-200 text-zinc-800 text-xs font-black shadow-lg hover:bg-slate-50 hover:border-zinc-300 transition-all active:scale-95">
            <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <span>Tìm ở khu vực này</span>
        </button>

        {{-- 📱 Mobile Floating Search Bar Card --}}
        <div class="md:hidden absolute top-4 left-4 right-4 z-20 bg-white/95 backdrop-blur-md border border-zinc-200/80 shadow-lg rounded-2xl p-2.5 flex items-center gap-2">
            <button id="mobile-gps-btn" class="p-2 bg-emerald-50 text-emerald-600 rounded-xl hover:bg-emerald-100 transition-colors" title="Định vị">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                </svg>
            </button>
            <div class="flex-1 text-xs font-bold text-zinc-700 truncate px-1" id="mobile-search-display">
                Đang tìm sân quanh bạn...
            </div>
            <div class="h-6 w-px bg-zinc-200"></div>
            <button id="mobile-search-trigger" class="p-2 hover:bg-slate-50 rounded-xl text-zinc-500">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </button>
        </div>

        {{-- 📱 Mobile Floating Filter Trigger --}}
        <button id="mobile-filter-trigger" class="md:hidden fixed bottom-6 left-1/2 -translate-x-1/2 z-20 flex items-center gap-2 px-5 py-3 rounded-full bg-zinc-950 text-white text-sm font-bold shadow-xl hover:bg-emerald-600 transition-all duration-200 active:scale-95">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
            </svg>
            <span>Bộ lọc</span>
        </button>
    </div>

    {{-- ==========================================================================
         MOBILE BOTTOM SHEET (Bottom Sheet for Cards)
         ========================================================================== --}}
    <div id="mobile-sheet" class="md:hidden fixed bottom-0 left-0 right-0 bg-white rounded-t-3xl shadow-[0_-8px_30px_rgba(0,0,0,0.12)] z-30 transition-transform duration-300 ease-out translate-y-[60%] flex flex-col h-[75vh]">
        <!-- Pull handle -->
        <div id="mobile-sheet-drag" class="flex flex-col items-center py-3 cursor-pointer shrink-0 border-b border-zinc-100">
            <div class="w-10 h-1.5 bg-zinc-300 rounded-full mb-1.5"></div>
            <span class="text-[10px] text-zinc-400 font-extrabold uppercase tracking-widest select-none">Kéo lên để xem danh sách</span>
        </div>
        <!-- Body scrollable list -->
        <div id="mobile-sheet-content" class="flex-1 overflow-y-auto px-4 py-5 bg-slate-50/50">
            <!-- Mobile result count -->
            <div id="mobile-results-count" class="text-xs font-extrabold text-zinc-800 uppercase tracking-wider mb-4">Danh sách sân</div>
            <!-- Mobile Cards Container -->
            <div id="mobile-cards-container" class="space-y-4"></div>
        </div>
    </div>

    {{-- ==========================================================================
         MOBILE FILTER MODAL
         ========================================================================== --}}
    <div id="filter-modal" class="hidden fixed inset-0 bg-zinc-950/60 backdrop-blur-sm z-[9999] flex items-end justify-center">
        <div class="bg-white w-full rounded-t-3xl max-h-[90vh] overflow-y-auto flex flex-col p-6 transition-all duration-300 translate-y-full" id="filter-modal-content">
            <div class="flex items-center justify-between border-b border-zinc-100 pb-4 mb-5">
                <h3 class="text-base font-black text-zinc-900 tracking-tight">Bộ lọc tìm kiếm</h3>
                <button id="close-filter-modal" class="p-1 rounded-full hover:bg-slate-100 text-zinc-400 hover:text-zinc-600 transition-colors">
                    <svg class="w-5.5 h-5.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <div class="space-y-6">
                <!-- Keyword search -->
                <div>
                    <label class="block text-[10px] font-bold text-zinc-400 uppercase tracking-wider mb-2">Tìm kiếm từ khóa</label>
                    <input id="mobile-keyword-search" type="text" placeholder="Nhập tên sân hoặc địa chỉ..." class="w-full rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm font-semibold text-zinc-800 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all">
                </div>
                
                <!-- Sports selection -->
                <div>
                    <label class="block text-[10px] font-bold text-zinc-400 uppercase tracking-wider mb-2">Bộ môn thể thao</label>
                    <div class="grid grid-cols-2 gap-2" id="mobile-sport-container"></div>
                </div>
                
                <!-- Distance selection -->
                <div>
                    <label class="block text-[10px] font-bold text-zinc-400 uppercase tracking-wider mb-2">Bán kính tìm kiếm</label>
                    <div class="grid grid-cols-3 gap-2" id="mobile-radius-container"></div>
                </div>
            </div>
            
            <div class="flex items-center gap-3 border-t border-zinc-100 pt-5 mt-6">
                <button onclick="resetFilters(); closeMobileFilterModal();" class="flex-1 py-3 border border-zinc-200 text-zinc-700 text-sm font-bold rounded-xl hover:bg-slate-50 active:scale-95 transition-all">
                    Reset lọc
                </button>
                <button id="apply-filters-btn" class="flex-1 py-3 bg-emerald-500 text-white text-sm font-bold rounded-xl hover:bg-emerald-600 shadow-md shadow-emerald-500/10 active:scale-95 transition-all">
                    Áp dụng
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
{{-- Leaflet.js and MarkerCluster CDN --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>

<script>
/* ──────────────────────────────────────────────────────────────────────────
   NEARBY VENUES - STATE & INTERACTION LOGIC
   ────────────────────────────────────────────────────────────────────────── */
let map, userMarker, accuracyCircle;
let venueMarkers = {};
let venuesData = [];
let userLat = null, userLng = null;

// Default Coords: Hanoi Center
const defaultLat = 21.028511, defaultLng = 105.804817;

// Filter State Object
const filterState = {
    sportId: 'all',
    radius: '5',
    keyword: '',
    sortBy: 'distance',
    openOnly: false
};

// DOM references
const gpsBtn = document.getElementById('gps-btn');
const mobileGpsBtn = document.getElementById('mobile-gps-btn');
const gpsLabel = document.getElementById('gps-label');
const gpsStatusBadge = document.getElementById('gps-status-badge');
const keywordInput = document.getElementById('keyword-search');
const searchBtn = document.getElementById('search-btn');
const cardsContainer = document.getElementById('cards-container');
const stateLoading = document.getElementById('state-loading');
const stateEmpty = document.getElementById('state-empty');
const resultsCountEl = document.getElementById('results-count');
const searchHereBtn = document.getElementById('search-here-btn');
const openOnlyToggle = document.getElementById('open-only-toggle');

// Mobile DOM references
const mobileCardsContainer = document.getElementById('mobile-cards-container');
const mobileResultsCountEl = document.getElementById('mobile-results-count');
const mobileSearchDisplay = document.getElementById('mobile-search-display');
const mobileSearchTrigger = document.getElementById('mobile-search-trigger');
const filterModal = document.getElementById('filter-modal');
const filterModalContent = document.getElementById('filter-modal-content');
const mobileFilterTrigger = document.getElementById('mobile-filter-trigger');
const closeFilterModal = document.getElementById('close-filter-modal');
const applyFiltersBtn = document.getElementById('apply-filters-btn');
const mobileSheet = document.getElementById('mobile-sheet');
const mobileSheetDrag = document.getElementById('mobile-sheet-drag');

const isAuth = {{ auth()->check() ? 'true' : 'false' }};
let markerClusterGroup;

/* ── Init ── */
document.addEventListener('DOMContentLoaded', () => {
    initMap();
    fetchVenues();
    bindEvents();
    setupMobileDrag();
});

/* ── Init Leaflet Map with Marker Clustering ── */
function initMap() {
    map = L.map('map', { zoomControl: false }).setView([defaultLat, defaultLng], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    L.control.zoom({ position: 'topright' }).addTo(map);

    // Initialize marker cluster group with beautiful visuals
    markerClusterGroup = L.markerClusterGroup({
        showCoverageOnHover: false,
        zoomToBoundsOnClick: true,
        spiderfyOnMaxZoom: true,
        maxClusterRadius: 40
    });
    map.addLayer(markerClusterGroup);

    // Map drag triggers search here button
    map.on('dragend', () => {
        searchHereBtn.classList.remove('hidden');
    });

    // Map click sets custom search coordinates
    map.on('click', (e) => {
        // Safe check to avoid clicking map popup elements
        if (e.originalEvent.target.closest('.custom-marker-bubble') || e.originalEvent.target.closest('.leaflet-popup-content-wrapper')) {
            return;
        }

        userLat = e.latlng.lat;
        userLng = e.latlng.lng;

        gpsBtn.classList.add('bg-emerald-500', 'text-white', 'border-emerald-500');
        gpsLabel.textContent = 'Đã đặt vị trí bản đồ';
        gpsStatusBadge.textContent = 'Tâm bản đồ';
        gpsStatusBadge.classList.replace('bg-zinc-200/60', 'bg-emerald-50');
        gpsStatusBadge.classList.replace('text-zinc-500', 'text-emerald-600');

        if (mobileSearchDisplay) {
            mobileSearchDisplay.textContent = `Tâm: ${userLat.toFixed(4)}, ${userLng.toFixed(4)}`;
        }

        placeUserMarker(userLat, userLng, 10);
        fetchVenues();
        showToast('Đã chuyển vị trí tâm tìm kiếm sang điểm click!', 'success');
    });
}

/* ── Event Listeners Binding ── */
function bindEvents() {
    // GPS Actions
    gpsBtn.addEventListener('click', handleGPS);
    if (mobileGpsBtn) mobileGpsBtn.addEventListener('click', handleGPS);

    // Search Here Button Action
    searchHereBtn.addEventListener('click', () => {
        const center = map.getCenter();
        userLat = center.lat;
        userLng = center.lng;

        gpsBtn.classList.add('bg-emerald-500', 'text-white', 'border-emerald-500');
        gpsLabel.textContent = 'Đã đặt vị trí bản đồ';
        gpsStatusBadge.textContent = 'Tâm bản đồ';

        if (mobileSearchDisplay) {
            mobileSearchDisplay.textContent = `Tâm: ${userLat.toFixed(4)}, ${userLng.toFixed(4)}`;
        }

        placeUserMarker(userLat, userLng, 10);
        fetchVenues();
        searchHereBtn.classList.add('hidden');
        showToast('Đã quét các sân tại vùng bản đồ này.', 'success');
    });

    // Sorting Option Actions
    document.querySelectorAll('.sort-chip').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.sort-chip').forEach(c => {
                c.classList.remove('bg-zinc-950', 'text-white', 'font-bold', 'shadow-sm');
                c.classList.add('bg-white', 'border', 'border-zinc-200', 'text-zinc-600', 'font-semibold');
            });
            btn.classList.add('bg-zinc-950', 'text-white', 'font-bold', 'shadow-sm');
            btn.classList.remove('bg-white', 'border', 'border-zinc-200', 'text-zinc-600', 'font-semibold');

            filterState.sortBy = btn.dataset.sort;
            renderVenues(venuesData);
        });
    });

    // Open Only Toggle Action
    openOnlyToggle.addEventListener('change', () => {
        filterState.openOnly = openOnlyToggle.checked;
        renderVenues(venuesData);
    });

    // Desktop Filters Action
    document.querySelectorAll('.sport-chip').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.sport-chip').forEach(c => {
                c.classList.remove('bg-emerald-500', 'border-emerald-500', 'text-white', 'shadow-sm');
                c.classList.add('bg-white', 'text-zinc-700', 'border-zinc-200');
            });
            btn.classList.add('bg-emerald-500', 'border-emerald-500', 'text-white', 'shadow-sm');
            btn.classList.remove('bg-white', 'text-zinc-700', 'border-zinc-200');
            
            filterState.sportId = btn.dataset.id;
            fetchVenues();
        });
    });

    document.querySelectorAll('.dist-chip').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.dist-chip').forEach(c => {
                c.classList.remove('bg-emerald-500', 'border-emerald-500', 'text-white', 'shadow-sm');
                c.classList.add('bg-white', 'text-zinc-700', 'border-zinc-200');
            });
            btn.classList.add('bg-emerald-500', 'border-emerald-500', 'text-white', 'shadow-sm');
            btn.classList.remove('bg-white', 'text-zinc-700', 'border-zinc-200');

            filterState.radius = btn.dataset.radius;
            if (!userLat && filterState.radius !== 'all') {
                showToast('Định vị GPS để lọc bán kính chính xác nhất!', 'warning');
            }
            fetchVenues();
        });
    });

    // Keyword Search Actions
    searchBtn.addEventListener('click', () => {
        filterState.keyword = keywordInput.value.trim();
        fetchVenues();
    });
    keywordInput.addEventListener('keypress', e => {
        if (e.key === 'Enter') {
            filterState.keyword = keywordInput.value.trim();
            fetchVenues();
        }
    });

    // Mobile Modal Trigger Events
    if (mobileSearchTrigger) mobileSearchTrigger.addEventListener('click', openMobileFilters);
    if (mobileSearchDisplay) mobileSearchDisplay.addEventListener('click', openMobileFilters);
    if (mobileFilterTrigger) mobileFilterTrigger.addEventListener('click', openMobileFilters);

    closeFilterModal.addEventListener('click', closeMobileFilterModal);
    filterModal.addEventListener('click', e => {
        if (e.target === filterModal) closeMobileFilterModal();
    });

    applyFiltersBtn.addEventListener('click', () => {
        // Sync mobile values to desktop inputs
        const mobKw = document.getElementById('mobile-keyword-search').value.trim();
        keywordInput.value = mobKw;
        filterState.keyword = mobKw;

        // Fetch using state synced from mobile selectors
        fetchVenues();
        closeMobileFilterModal();
    });
}

/* ── Swipe Drag Mechanics for Mobile Bottom Sheet ── */
let isSheetExpanded = false;
function setupMobileDrag() {
    let startY = 0;
    let startTranslate = 60;
    let currentTranslate = 60;

    mobileSheetDrag.addEventListener('touchstart', (e) => {
        startY = e.touches[0].clientY;
        startTranslate = isSheetExpanded ? 0 : 60;
        mobileSheet.style.transition = 'none';
    });

    mobileSheetDrag.addEventListener('touchmove', (e) => {
        const deltaY = e.touches[0].clientY - startY;
        const deltaPercent = (deltaY / window.innerHeight) * 100;
        currentTranslate = startTranslate + deltaPercent;

        if (currentTranslate < 0) currentTranslate = 0;
        if (currentTranslate > 70) currentTranslate = 70;

        mobileSheet.style.transform = `translateY(${currentTranslate}%)`;
    });

    mobileSheetDrag.addEventListener('touchend', (e) => {
        mobileSheet.style.transition = 'transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        if (currentTranslate < 30) {
            mobileSheet.style.transform = 'translateY(0%)';
            isSheetExpanded = true;
            mobileSheetDrag.querySelector('span').textContent = 'Kéo xuống để thu nhỏ';
        } else {
            mobileSheet.style.transform = 'translateY(60%)';
            isSheetExpanded = false;
            mobileSheetDrag.querySelector('span').textContent = 'Kéo lên để xem danh sách';
        }
    });

    // Tap handle to toggle
    mobileSheetDrag.addEventListener('click', () => {
        mobileSheet.style.transition = 'transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        isSheetExpanded = !isSheetExpanded;
        if (isSheetExpanded) {
            mobileSheet.style.transform = 'translateY(0%)';
            mobileSheetDrag.querySelector('span').textContent = 'Kéo xuống để thu nhỏ';
        } else {
            mobileSheet.style.transform = 'translateY(60%)';
            mobileSheetDrag.querySelector('span').textContent = 'Kéo lên để xem danh sách';
        }
    });
}

/* ── Mobile Filters Modal Handling ── */
function openMobileFilters() {
    filterModal.classList.remove('hidden');
    // Force layout engine recalculation
    filterModal.offsetHeight;
    filterModalContent.classList.remove('translate-y-full');
    
    // Set keyword search input value in modal
    document.getElementById('mobile-keyword-search').value = filterState.keyword;
    
    // Sync UI chips
    syncMobileFiltersUI();
}

function closeMobileFilterModal() {
    filterModalContent.classList.add('translate-y-full');
    setTimeout(() => {
        filterModal.classList.add('hidden');
    }, 300);
}

function syncMobileFiltersUI() {
    // Populate Sports Grid inside Mobile Modal
    const mobSportCont = document.getElementById('mobile-sport-container');
    mobSportCont.innerHTML = '';

    // Standard All chip
    const allActive = filterState.sportId === 'all';
    const allBtn = document.createElement('button');
    allBtn.className = `py-2.5 text-xs font-bold rounded-xl border transition-all ${allActive ? 'bg-emerald-500 border-emerald-500 text-white shadow-sm' : 'bg-white border-zinc-200 text-zinc-700'}`;
    allBtn.textContent = '🎾 Tất cả môn';
    allBtn.addEventListener('click', () => {
        filterState.sportId = 'all';
        syncMobileFiltersUI();
    });
    mobSportCont.appendChild(allBtn);

    // Map each sport to grid item
    document.querySelectorAll('.sport-chip[data-id]:not([data-id="all"])').forEach(c => {
        const id = c.dataset.id;
        const name = c.textContent.trim();
        const active = filterState.sportId === id;
        
        const btn = document.createElement('button');
        btn.className = `py-2.5 text-xs font-bold rounded-xl border transition-all ${active ? 'bg-emerald-500 border-emerald-500 text-white shadow-sm' : 'bg-white border-zinc-200 text-zinc-700'}`;
        btn.textContent = name;
        btn.addEventListener('click', () => {
            filterState.sportId = id;
            syncMobileFiltersUI();
        });
        mobSportCont.appendChild(btn);
    });

    // Populate Radius Grid inside Mobile Modal
    const mobRadCont = document.getElementById('mobile-radius-container');
    mobRadCont.innerHTML = '';
    
    const radiusOptions = [
        { val: '1', label: '1 km' },
        { val: '3', label: '3 km' },
        { val: '5', label: '5 km' },
        { val: '10', label: '10 km' },
        { val: 'all', label: 'Tất cả' }
    ];

    radiusOptions.forEach(opt => {
        const active = filterState.radius === opt.val;
        const btn = document.createElement('button');
        btn.className = `py-2 px-1 text-xs font-bold rounded-xl border transition-all ${active ? 'bg-emerald-500 border-emerald-500 text-white shadow-sm' : 'bg-white border-zinc-200 text-zinc-700'}`;
        btn.textContent = opt.label;
        btn.addEventListener('click', () => {
            filterState.radius = opt.val;
            syncMobileFiltersUI();
        });
        mobRadCont.appendChild(btn);
    });
}

/* ── Geolocation Handling ── */
function handleGPS() {
    if (!navigator.geolocation) {
        showToast('Trình duyệt của bạn không hỗ trợ định vị GPS.', 'error');
        return;
    }

    gpsBtn.disabled = true;
    if (mobileGpsBtn) mobileGpsBtn.disabled = true;
    gpsLabel.textContent = 'Đang lấy vị trí...';
    gpsStatusBadge.textContent = 'Đang quét...';
    gpsStatusBadge.classList.replace('bg-zinc-200/60', 'bg-amber-100');
    gpsStatusBadge.classList.replace('text-zinc-500', 'text-amber-600');

    navigator.geolocation.getCurrentPosition(
        pos => {
            userLat = pos.coords.latitude;
            userLng = pos.coords.longitude;

            gpsBtn.disabled = false;
            if (mobileGpsBtn) mobileGpsBtn.disabled = false;
            gpsBtn.classList.add('bg-emerald-500', 'text-white', 'border-emerald-500');
            gpsLabel.textContent = 'Đã cập nhật vị trí';
            
            gpsStatusBadge.textContent = 'Đã định vị';
            gpsStatusBadge.classList.replace('bg-amber-100', 'bg-emerald-50');
            gpsStatusBadge.classList.replace('text-amber-600', 'text-emerald-600');

            if (mobileSearchDisplay) {
                mobileSearchDisplay.textContent = `Vị trí: ${userLat.toFixed(4)}, ${userLng.toFixed(4)}`;
            }

            placeUserMarker(userLat, userLng, pos.coords.accuracy);
            fetchVenues();
            showToast('Định vị thành công! Đang lọc các sân gần nhất quanh bạn.', 'success');
        },
        err => {
            gpsBtn.disabled = false;
            if (mobileGpsBtn) mobileGpsBtn.disabled = false;
            gpsLabel.textContent = 'Định vị vị trí của tôi';
            
            gpsStatusBadge.textContent = 'Lỗi định vị';
            gpsStatusBadge.classList.replace('bg-amber-100', 'bg-rose-100');
            gpsStatusBadge.classList.replace('text-amber-600', 'text-rose-600');

            let msg = 'Không thể định vị GPS.';
            if (err.code === err.PERMISSION_DENIED) msg = 'Vui lòng cho phép quyền vị trí trong trình duyệt của bạn.';
            else if (err.code === err.POSITION_UNAVAILABLE) msg = 'Không bắt được tín hiệu vị trí.';
            else if (err.code === err.TIMEOUT) msg = 'Định vị quá hạn phản hồi.';
            
            showToast(msg, 'error');
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
}

/* ── Place Pulsing Geolocation Marker ── */
function placeUserMarker(lat, lng, accuracy) {
    if (userMarker) map.removeLayer(userMarker);
    if (accuracyCircle) map.removeLayer(accuracyCircle);

    const icon = L.divIcon({
        className: 'user-pin',
        iconSize: [20, 20], iconAnchor: [10, 10]
    });
    
    userMarker = L.marker([lat, lng], { icon })
        .addTo(map)
        .bindPopup('<b style="font-size:13px;color:#0f172a">Vị trí hiện tại của bạn</b>');

    accuracyCircle = L.circle([lat, lng], {
        radius: accuracy, color: '#3B82F6',
        fillColor: '#3B82F6', fillOpacity: .08, weight: 1.5
    }).addTo(map);

    map.setView([lat, lng], 14);
}

/* ── Fetch API Data ── */
async function fetchVenues() {
    showState('loading');

    const url = new URL('/api/venues/nearby', window.location.origin);
    if (userLat !== null && userLng !== null) {
        url.searchParams.set('lat', userLat);
        url.searchParams.set('lng', userLng);
        url.searchParams.set('radius', filterState.radius);
    }
    url.searchParams.set('sport_id', filterState.sportId);
    if (filterState.keyword) {
        url.searchParams.set('q', filterState.keyword);
    }

    try {
        const res = await fetch(url, { headers: { Accept: 'application/json' } });
        if (!res.ok) throw new Error('API request failed: ' + res.status);
        const json = await res.json();
        venuesData = json.data || [];
        renderVenues(venuesData);
    } catch (err) {
        console.error(err);
        showToast('Lỗi tải dữ liệu. Vui lòng kết nối lại máy chủ.', 'error');
        showState('empty');
    }
}

/* ── Dynamic Open/Close Hour Check ── */
function checkIfOpen(openTime, closeTime) {
    if (!openTime || !closeTime) return true;
    const now = new Date();
    const currentH = now.getHours();
    const currentM = now.getMinutes();
    const currentS = now.getSeconds();
    const currentTimeStr = `${currentH.toString().padStart(2, '0')}:${currentM.toString().padStart(2, '0')}:${currentS.toString().padStart(2, '0')}`;
    return currentTimeStr >= openTime && currentTimeStr <= closeTime;
}

/* ── Main Render Loop ── */
function renderVenues(venues) {
    // Clear active map layers
    markerClusterGroup.clearLayers();
    venueMarkers = {};
    
    // Clear lists
    cardsContainer.innerHTML = '';
    if (mobileCardsContainer) mobileCardsContainer.innerHTML = '';

    // Apply Client-side Filters
    let processedVenues = [...venues];
    
    // 1. Filter: "Đang mở cửa"
    if (filterState.openOnly) {
        processedVenues = processedVenues.filter(v => checkIfOpen(v.open_hours, v.close_hours));
    }

    // 2. Sorting
    if (filterState.sortBy === 'distance') {
        processedVenues.sort((a, b) => {
            if (a.distance === null) return 1;
            if (b.distance === null) return -1;
            return a.distance - b.distance;
        });
    } else if (filterState.sortBy === 'rating') {
        processedVenues.sort((a, b) => {
            const rA = a.reviews_avg_rating ? parseFloat(a.reviews_avg_rating) : 5.0;
            const rB = b.reviews_avg_rating ? parseFloat(b.reviews_avg_rating) : 5.0;
            return rB - rA;
        });
    }

    // Sync state headers
    const countText = `${processedVenues.length} sân được tìm thấy`;
    resultsCountEl.textContent = countText;
    if (mobileResultsCountEl) mobileResultsCountEl.textContent = countText;

    if (!processedVenues.length) {
        showState('empty');
        return;
    }

    const bounds = [];
    if (userLat && userLng) bounds.push([userLat, userLng]);

    processedVenues.forEach(v => {
        const lat = parseFloat(v.lat), lng = parseFloat(v.lng);
        if (!isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0) {
            bounds.push([lat, lng]);
        }

        // Retrieve rating and reviews count from database
        const rating = v.reviews_avg_rating ? parseFloat(v.reviews_avg_rating).toFixed(1) : '5.0';
        const reviewsCount = v.reviews_count || 0;
        const isOpen = checkIfOpen(v.open_hours, v.close_hours);
        
        // Clean formats
        const openTime = v.open_hours ? v.open_hours.substring(0, 5) : '06:00';
        const closeTime = v.close_hours ? v.close_hours.substring(0, 5) : '22:00';

        // 1. Popup HTML inside marker
        const popupHtml = `
            <div style="margin:-14px; font-family:'Inter',sans-serif; width:240px; overflow:hidden; border-radius:12px;">
                <img src="${v.banner || 'https://images.unsplash.com/photo-1545224497-5d750cdef99d?w=500'}" style="height:100px; width:100%; object-fit:cover;" alt="${v.name}">
                <div style="padding:12px;">
                    <div style="font-weight:800; font-size:14px; color:#111827; margin-bottom:4px; line-height:1.3;">${v.name}</div>
                    <div style="display:flex; align-items:center; gap:4px; margin-bottom:6px;">
                        <span style="color:#f59e0b; font-size:12px;">⭐ ${rating}</span>
                        <span style="color:#6b7280; font-size:11px;">(${reviewsCount} đánh giá)</span>
                    </div>
                    <div style="font-size:11px; color:#6b7280; display:flex; align-items:flex-start; gap:4px; margin-bottom:10px;">
                        <span style="flex-shrink:0;">📍</span>
                        <span style="line-clamp:2; display:-webkit-box; -webkit-box-orient:vertical; overflow:hidden;">${v.address}</span>
                    </div>
                    <a href="/venues/${v.id}" style="display:block; text-align:center; background:#10B981; color:#fff !important; font-size:11px; font-weight:700; padding:8px 0; border-radius:8px; text-decoration:none; transition:background .2s;">
                        Chi tiết & Đặt lịch
                    </a>
                </div>
            </div>`;

        // 2. Custom rating marker bubble using L.divIcon
        const customMarkerIcon = L.divIcon({
            className: 'custom-map-marker-container',
            html: `
                <div class="custom-marker-bubble transition-all duration-300" id="map-pin-${v.id}">
                    <span style="color:#f59e0b; font-size:10px;">★</span>
                    <span>${rating}</span>
                </div>
            `,
            iconSize: [46, 24],
            iconAnchor: [23, 24]
        });

        // Add Marker
        const marker = L.marker([lat, lng], { icon: customMarkerIcon }).bindPopup(popupHtml);
        markerClusterGroup.addLayer(marker);
        venueMarkers[v.id] = marker;

        // Two-way interactive hover triggers
        marker.on('mouseover', () => {
            const pin = document.getElementById(`map-pin-${v.id}`);
            if (pin) pin.classList.add('active-highlight');
            
            // Highlight list card
            const dCard = document.getElementById(`vc-${v.id}`);
            if (dCard) dCard.classList.add('active');

            const mCard = document.getElementById(`vc-m-${v.id}`);
            if (mCard) mCard.classList.add('active');
        });

        marker.on('mouseout', () => {
            const pin = document.getElementById(`map-pin-${v.id}`);
            if (pin) pin.classList.remove('active-highlight');
            
            const dCard = document.getElementById(`vc-${v.id}`);
            if (dCard && !dCard.dataset.pinned) dCard.classList.remove('active');

            const mCard = document.getElementById(`vc-m-${v.id}`);
            if (mCard && !mCard.dataset.pinned) mCard.classList.remove('active');
        });

        marker.on('click', () => {
            activateCard(v.id);
            scrollCard(v.id);
        });

        // 3. Card Element Blueprint (Horizontal layout)
        const cardInnerHtml = `
            <div class="relative w-[130px] h-full overflow-hidden bg-slate-100 shrink-0">
                <img class="w-full h-full object-cover transition-transform duration-500 hover:scale-105" src="${v.banner || 'https://images.unsplash.com/photo-1545224497-5d750cdef99d?w=500'}" alt="${v.name}" loading="lazy">
                <span class="absolute top-2 left-2 bg-white/95 backdrop-blur-sm text-[9px] font-extrabold text-emerald-600 px-2 py-0.5 rounded shadow-sm uppercase tracking-wider">${v.sport.name}</span>
                ${v.distance !== null ? `<span class="absolute bottom-2 left-2 bg-zinc-900/85 backdrop-blur-sm text-white text-[10px] font-bold px-2 py-0.5 rounded flex items-center gap-0.5 shadow-sm">📍 ${v.distance}km</span>` : ''}
            </div>
            <div class="flex-1 p-3.5 flex flex-col justify-between overflow-hidden">
                <div>
                    <div class="flex items-start justify-between gap-1.5 mb-1">
                        <h3 class="text-sm font-extrabold text-zinc-900 leading-snug line-clamp-1 pr-1" title="${v.name}">${v.name}</h3>
                        <div class="flex items-center gap-0.5 shrink-0 bg-amber-50 text-amber-600 px-1.5 py-0.5 rounded text-[10px] font-extrabold shadow-sm">
                            ⭐ <span class="text-zinc-800">${rating}</span>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-1 text-[11px] text-zinc-500 mb-1.5">
                        <svg class="w-3.5 h-3.5 text-zinc-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                        </svg>
                        <span class="truncate" title="${v.address}">${v.address}</span>
                    </div>

                    <div class="flex items-center gap-2 text-[11px] font-bold">
                        <span class="${isOpen ? 'text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded' : 'text-rose-600 bg-rose-50 px-1.5 py-0.5 rounded'}">
                            ${isOpen ? '🟢 Đang mở' : '🔴 Đóng cửa'}
                        </span>
                        <span class="text-zinc-300">•</span>
                        <span class="text-zinc-500 font-semibold">${v.courts_count} sân hoạt động</span>
                    </div>
                </div>

                
                    <div class="flex items-center gap-1.5">
                        ${v.phone ? `
                        <a href="tel:${v.phone}" onclick="event.stopPropagation();" class="p-1.5 rounded-lg border border-zinc-200 hover:border-zinc-300 hover:bg-slate-50 transition-all text-zinc-500" title="Gọi Hotline">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/>
                            </svg>
                        </a>` : ''}
                        
                        ${isAuth ? `
                        <button id="fav-${v.id}" onclick="toggleFav(event,${v.id})" class="p-1.5 rounded-lg border border-zinc-200 hover:border-zinc-300 hover:bg-rose-50 hover:text-rose-600 transition-all text-zinc-400" title="Yêu thích">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/>
                            </svg>
                        </button>` : ''}

                        <a href="/venues/${v.id}" class="text-[11px] font-extrabold bg-zinc-950 text-white hover:bg-emerald-500 hover:shadow-md hover:shadow-emerald-500/15 active:scale-95 transition-all px-3 py-1.5 rounded-lg text-decoration-none">
                            Xem Sân
                        </a>
                    </div>
                </div>
            </div>`;

        // Render Desktop Card
        const dCard = document.createElement('div');
        dCard.id = `vc-${v.id}`;
        dCard.className = 'vcard flex h-[150px]';
        dCard.innerHTML = cardInnerHtml;
        
        dCard.addEventListener('click', () => handleCardClick(v.id, lat, lng));
        dCard.addEventListener('mouseenter', () => {
            dCard.classList.add('active');
            const pin = document.getElementById(`map-pin-${v.id}`);
            if (pin) pin.classList.add('active-highlight');
        });
        dCard.addEventListener('mouseleave', () => {
            if (!dCard.dataset.pinned) {
                dCard.classList.remove('active');
                const pin = document.getElementById(`map-pin-${v.id}`);
                if (pin) pin.classList.remove('active-highlight');
            }
        });
        cardsContainer.appendChild(dCard);

        // Render Mobile Card
        if (mobileCardsContainer) {
            const mCard = document.createElement('div');
            mCard.id = `vc-m-${v.id}`;
            mCard.className = 'vcard flex h-[150px]';
            mCard.innerHTML = cardInnerHtml;
            
            mCard.addEventListener('click', () => {
                handleCardClick(v.id, lat, lng);
                // Minimize sheet on card click to show map details
                mobileSheet.style.transition = 'transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
                mobileSheet.style.transform = 'translateY(60%)';
                isSheetExpanded = false;
                mobileSheetDrag.querySelector('span').textContent = 'Kéo lên để xem danh sách';
            });
            mobileCardsContainer.appendChild(mCard);
        }
    });

    showState('cards');

    // Smooth pan-fit coordinates bounds logic
    if (bounds.length > 0) {
        map.fitBounds(bounds, { padding: [50, 50], maxZoom: 15 });
    }
}

/* ── UI Visual State Toggling ── */
function showState(state) {
    if (state === 'loading') {
        stateLoading.classList.remove('hidden');
        cardsContainer.classList.add('hidden');
        stateEmpty.classList.add('hidden');
    } else if (state === 'cards') {
        stateLoading.classList.add('hidden');
        cardsContainer.classList.remove('hidden');
        stateEmpty.classList.add('hidden');
    } else {
        stateLoading.classList.add('hidden');
        cardsContainer.classList.add('hidden');
        stateEmpty.classList.remove('hidden');
    }
}

/* ── Card Click Visual Sync ── */
function handleCardClick(id, lat, lng) {
    activateCard(id);
    map.setView([lat, lng], 15);
    
    // Trigger popups
    setTimeout(() => {
        venueMarkers[id]?.openPopup();
    }, 100);
}

function activateCard(id) {
    // Desktop cards highlight clean
    document.querySelectorAll('.vcard').forEach(c => {
        c.classList.remove('active');
        delete c.dataset.pinned;
    });
    
    // Highlight matching desktop element
    const dEl = document.getElementById(`vc-${id}`);
    if (dEl) {
        dEl.classList.add('active');
        dEl.dataset.pinned = '1';
    }

    // Highlight matching mobile element
    const mEl = document.getElementById(`vc-m-${id}`);
    if (mEl) {
        mEl.classList.add('active');
        mEl.dataset.pinned = '1';
    }

    // Highlight matching map pins
    document.querySelectorAll('.custom-marker-bubble').forEach(p => p.classList.remove('active-highlight'));
    const pin = document.getElementById(`map-pin-${id}`);
    if (pin) pin.classList.add('active-highlight');
}

function scrollCard(id) {
    const el = document.getElementById(`vc-${id}`);
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

/* ── Reset Filter Chips Helper ── */
function resetFilters() {
    // Reset inputs
    keywordInput.value = '';
    filterState.keyword = '';
    filterState.sportId = 'all';
    filterState.radius = '5';
    filterState.openOnly = false;
    openOnlyToggle.checked = false;

    // Clear active classes in filters
    document.querySelectorAll('.sport-chip').forEach(c => {
        c.classList.remove('bg-emerald-500', 'border-emerald-500', 'text-white', 'shadow-sm');
        c.classList.add('bg-white', 'text-zinc-700', 'border-zinc-200');
        if (c.dataset.id === 'all') {
            c.classList.add('bg-emerald-500', 'border-emerald-500', 'text-white', 'shadow-sm');
            c.classList.remove('bg-white', 'text-zinc-700', 'border-zinc-200');
        }
    });

    document.querySelectorAll('.dist-chip').forEach(c => {
        c.classList.remove('bg-emerald-500', 'border-emerald-500', 'text-white', 'shadow-sm');
        c.classList.add('bg-white', 'text-zinc-700', 'border-zinc-200');
        if (c.dataset.radius === '5') {
            c.classList.add('bg-emerald-500', 'border-emerald-500', 'text-white', 'shadow-sm');
            c.classList.remove('bg-white', 'text-zinc-700', 'border-zinc-200');
        }
    });

    if (mobileSearchDisplay) {
        mobileSearchDisplay.textContent = 'Đang tìm sân quanh bạn...';
    }

    fetchVenues();
}

/* ── Toggle Favorites via AJAX ── */
async function toggleFav(event, id) {
    event.stopPropagation();
    
    // Target both desktop and mobile buttons if they exist
    const dBtn = document.querySelector(`#vc-${id} button[id^="fav-"]`);
    const mBtn = document.querySelector(`#vc-m-${id} button[id^="fav-"]`);

    try {
        const res = await fetch(`/venues/${id}/favorite`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        const data = await res.json();
        
        if (res.ok) {
            const isAdded = data.status === 'added';
            
            // Sync buttons UI classes & colors
            [dBtn, mBtn].forEach(btn => {
                if (btn) {
                    const svg = btn.querySelector('svg');
                    if (isAdded) {
                        svg.setAttribute('fill', '#f43f5e');
                        svg.setAttribute('stroke', '#f43f5e');
                        btn.className = btn.className.replace('text-zinc-400', 'text-rose-500 bg-rose-50 border-rose-100');
                    } else {
                        svg.setAttribute('fill', 'none');
                        svg.setAttribute('stroke', 'currentColor');
                        btn.className = btn.className.replace('text-rose-500 bg-rose-50 border-rose-100', 'text-zinc-400 border-zinc-200');
                    }
                }
            });

            showToast(isAdded ? 'Đã thêm sân này vào mục yêu thích.' : 'Đã xóa sân này khỏi mục yêu thích.', 'success');
        } else {
            showToast('Bạn cần đăng nhập để thực hiện chức năng này.', 'warning');
        }
    } catch {
        showToast('Lỗi đường truyền kết nối máy chủ.', 'error');
    }
}
</script>
@endsection
