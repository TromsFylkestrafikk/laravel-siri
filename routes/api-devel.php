<?php

/**
 * Development routes
 */

use Illuminate\Support\Facades\Route;
use TromsFylkestrafikk\Siri\Http\Controllers\SiriDevelController;

Route::post('dev/subscribe/ok', [SiriDevelController::class, 'subscribeOk']);
Route::post('dev/subscribe/fail', [SiriDevelController::class, 'subscribeFailed']);
Route::get('dev/subscriptions', [SiriDevelController::class, 'subscriptions']);
