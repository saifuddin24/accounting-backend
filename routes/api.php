<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChartOfAccountController;
use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FiscalYearController;
use App\Http\Controllers\ContactController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public API for now
Route::prefix('v1')->group(function () {
    // Profiles
    Route::get('/profiles', [ProfileController::class, 'index']);
    Route::post('/profiles', [ProfileController::class, 'store']);
    Route::get('/profiles/{id}', [ProfileController::class, 'show']);
    Route::put('/profiles/{id}', [ProfileController::class, 'update']);

    // Fiscal Years
    Route::get('/fiscal-years', [FiscalYearController::class, 'index']);
    Route::post('/fiscal-years', [FiscalYearController::class, 'store']);
    Route::get('/fiscal-years/{id}', [FiscalYearController::class, 'show']);
    Route::put('/fiscal-years/{id}', [FiscalYearController::class, 'update']);

    // Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);

    // Accounts
    Route::get('/accounts', [ChartOfAccountController::class, 'index']);
    Route::post('/accounts', [ChartOfAccountController::class, 'store']);

    // Contacts
    Route::get('/contacts', [ContactController::class, 'index']);
    Route::post('/contacts', [ContactController::class, 'store']);
    Route::get('/contacts/{id}', [ContactController::class, 'show']);
    Route::put('/contacts/{id}', [ContactController::class, 'update']);
    Route::delete('/contacts/{id}', [ContactController::class, 'destroy']);

    // Journal Entries
    Route::get('/journals', [JournalEntryController::class, 'index']);
    Route::get('/journal-lines', [JournalEntryController::class, 'getLines']);
    Route::post('/journals', [JournalEntryController::class, 'store']);
    Route::post('/journals/quick', [JournalEntryController::class, 'storeQuick']);
    Route::get('/journals/{id}', [JournalEntryController::class, 'show']);
    Route::put('/journals/{id}', [JournalEntryController::class, 'update']);
    Route::delete('/journals/{id}', [JournalEntryController::class, 'destroy']);

    // Reports
    Route::get('/reports/ledger/{accountId}', [ReportController::class, 'getLedger']);
    Route::get('/reports/trial-balance', [ReportController::class, 'getTrialBalance']);
    Route::get('/reports/income-statement', [ReportController::class, 'getIncomeStatement']);
    Route::get('/reports/balance-sheet', [ReportController::class, 'getBalanceSheet']);
    Route::get('/reports/worksheet', [ReportController::class, 'getWorksheet']);

    // Helper
    Route::get('/ping', function () {
        return response()->json(['message' => 'Pong', 'time' => now()]);
    });
});
