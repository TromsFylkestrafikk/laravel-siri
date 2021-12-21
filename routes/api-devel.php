<?php

/**
 * Development routes
 */

use Illuminate\Support\Facades\Route;
use TromsFylkestrafikk\Siri\Http\Controllers\SiriDevelController;

Route::post('devel/subscribe/ok', [SiriDevelController::class, 'subscribeOk']);
Route::post('devel/subscribe/fail', [SiriDevelController::class, 'subscribeFailed']);
Route::get('devel/subscriptions', [SiriDevelController::class, 'subscriptions']);
