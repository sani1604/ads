<?php
// app/Http/Controllers/Api/ReportController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $stats = $this->reportService->getDashboardStats($user);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    public function chartData(Request $request)
    {
        $user = $request->user();
        $days = $request->get('days', 30);

        $chartData = $this->reportService->getChartData($user, $days);

        return response()->json([
            'success' => true,
            'data' => $chartData,
        ]);
    }
}