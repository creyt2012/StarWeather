<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\LiveStateController;

Route::middleware(['auth.api_key'])->prefix('v1')->group(function () {
    Route::get('/live/state', [LiveStateController::class, 'index']);
});
