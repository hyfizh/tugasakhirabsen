<?php

use App\Http\Controllers\Api\IotController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AbsensiController;

Route::prefix('iot')->group(function () {
    Route::post('/heartbeat', [IotController::class, 'heartbeat'])->name('api.iot.heartbeat');
    Route::post('/verify', [IotController::class, 'verify'])->name('api.iot.verify');
    Route::post('/log', [IotController::class, 'log'])->name('api.iot.log');
    Route::post('/absen', [AbsensiController::class, 'store']);
});
