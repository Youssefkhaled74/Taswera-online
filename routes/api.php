<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SyncJobController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    // Route::post('/sync-jobs', [SyncJobController::class, 'store']);
    Route::get('/sync-jobs/statistics', [SyncJobController::class, 'statistics']);
    Route::post('/branches', [SyncJobController::class, 'createBranch']);
    Route::get('/branches', [SyncJobController::class, 'listBranches']);
});


Route::middleware('branch.auth')->group(function () {
    Route::post('/sync-jobs', [SyncJobController::class, 'store']);
});