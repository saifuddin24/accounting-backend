<?php

namespace App\Http\Controllers;

use App\Services\Accounting\LedgerService;
use App\Services\Accounting\TrialBalanceService;
use App\Services\Accounting\IncomeStatementService;
use App\Services\Accounting\BalanceSheetService;
use App\Services\Accounting\WorksheetService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected $ledgerService;
    protected $trialBalanceService;
    protected $incomeStatementService;
    protected $balanceSheetService;
    protected $worksheetService;

    public function __construct(
        LedgerService $ledgerService,
        TrialBalanceService $trialBalanceService,
        IncomeStatementService $incomeStatementService,
        BalanceSheetService $balanceSheetService,
        WorksheetService $worksheetService
    ) {
        $this->ledgerService = $ledgerService;
        $this->trialBalanceService = $trialBalanceService;
        $this->incomeStatementService = $incomeStatementService;
        $this->balanceSheetService = $balanceSheetService;
        $this->worksheetService = $worksheetService;
    }

    public function getLedger(Request $request, $accountId)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $sortBy = $request->query('sort_by', 'date');
        $sortOrder = $request->query('sort_order', 'desc');

        try {
            $report = $this->ledgerService->getLedger($accountId, $startDate, $endDate, $sortBy, $sortOrder);
            return response()->json($report);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function getTrialBalance(Request $request)
    {
        $date = $request->query('date');
        $companyId = config('accounting.profile_id');

        try {
            $report = $this->trialBalanceService->getTrialBalance($companyId, $date);
            return response()->json($report);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getIncomeStatement(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $companyId = config('accounting.profile_id');

        try {
            $report = $this->incomeStatementService->getIncomeStatement($companyId, $startDate, $endDate);
            return response()->json($report);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getBalanceSheet(Request $request)
    {
        $date = $request->query('date');
        $companyId = config('accounting.profile_id');

        try {
            $report = $this->balanceSheetService->getBalanceSheet($companyId, $date);
            return response()->json($report);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getWorksheet(Request $request)
    {
        $date = $request->query('date');
        $companyId = config('accounting.profile_id');

        try {
            $report = $this->worksheetService->getWorksheet($companyId, $date);
            return response()->json($report);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
