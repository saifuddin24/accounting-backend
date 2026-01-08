<?php

namespace App\Services\Accounting;

use App\Models\JournalItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    protected $incomeStatementService;
    protected $balanceSheetService;

    public function __construct(IncomeStatementService $incomeStatementService, BalanceSheetService $balanceSheetService)
    {
        $this->incomeStatementService = $incomeStatementService;
        $this->balanceSheetService = $balanceSheetService;
    }

    public function getDashboardStats($companyId)
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        // 1. Get Income Statement for current month
        $incomeStatement = $this->incomeStatementService->getIncomeStatement($companyId, $startOfMonth, $endOfMonth);

        // 2. Get Balance Sheet for current date
        $balanceSheet = $this->balanceSheetService->getBalanceSheet($companyId, $now);

        // 3. Last month stats for trends (simplistic)
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();
        $lastMonthIncome = $this->incomeStatementService->getIncomeStatement($companyId, $startOfLastMonth, $endOfLastMonth);

        return [
            'total_assets' => $balanceSheet['total_assets'],
            'total_revenue' => $incomeStatement['total_revenues'],
            'total_expenses' => $incomeStatement['total_expenses'],
            'net_profit' => $incomeStatement['net_income'],
            'trends' => [
                'revenue' => $this->calculateTrend($incomeStatement['total_revenues'], $lastMonthIncome['total_revenues']),
                'expenses' => $this->calculateTrend($incomeStatement['total_expenses'], $lastMonthIncome['total_expenses']),
                'profit' => $this->calculateTrend($incomeStatement['net_income'], $lastMonthIncome['net_income']),
            ],
            'chart_data' => $this->getMonthlyFlow($companyId)
        ];
    }

    private function getMonthlyFlow($companyId)
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();
            
            $is = $this->incomeStatementService->getIncomeStatement($companyId, $start, $end);
            $data[] = [
                'month' => $month->format('M'),
                'revenue' => $is['total_revenues'],
                'expenses' => $is['total_expenses'],
            ];
        }
        return $data;
    }

    private function calculateTrend($current, $previous)
    {
        if ($previous == 0) return $current > 0 ? '+100%' : '0%';
        $change = (($current - $previous) / $previous) * 100;
        return ($change >= 0 ? '+' : '') . round($change, 1) . '%';
    }
}
