<?php

/**
 * Development routes
 */

use Illuminate\Support\Facades\Route;
use TromsFylkestrafikk\Siri\Http\Controllers\SiriDevController;

Route::post('dev/subscribe/ok', [SiriDevController::class, 'subscribeOk']);
Route::post('dev/subscribe/fail', [SiriDevController::class, 'subscribeFailed']);
Route::get('dev/subscriptions', [SiriDevController::class, 'subscriptions']);
