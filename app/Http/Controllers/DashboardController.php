<?php

namespace App\Http\Controllers;

use App\Services\Accounting\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function getStats(Request $request)
    {
        $companyId = config('accounting.profile_id');
        try {
            $stats = $this->dashboardService->getDashboardStats($companyId);
            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
