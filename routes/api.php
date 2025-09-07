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

// Frames
Route::post('/frames', [\App\Http\Controllers\FrameController::class, 'store']);
Route::get('/frames', [\App\Http\Controllers\FrameController::class, 'index']);
Route::get('/frames/{id}', [\App\Http\Controllers\FrameController::class, 'show']);

// Bulk delete frames and stickers
Route::post('/frames/delete-many', [\App\Http\Controllers\FrameController::class, 'destroyMany']);
Route::post('/stickers/delete-many', [\App\Http\Controllers\StickerController::class, 'destroyMany']);

// Stickers
Route::post('/stickers', [\App\Http\Controllers\StickerController::class, 'store']);
Route::get('/stickers', [\App\Http\Controllers\StickerController::class, 'index']);
Route::get('/stickers/{id}', [\App\Http\Controllers\StickerController::class, 'show']);