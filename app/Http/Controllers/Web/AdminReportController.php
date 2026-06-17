<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CourtReport;
use Illuminate\Http\Request;

class AdminReportController extends Controller
{
    public function index()
    {
        $reports = CourtReport::with(['user', 'court.venue'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.reports.index', compact('reports'));
    }

    public function updateStatus(Request $request, CourtReport $report)
    {
        $report->update(['status' => 'resolved']);
        return back()->with('success', 'Đã đánh dấu báo cáo là Đã xử lý thành công!');
    }
}