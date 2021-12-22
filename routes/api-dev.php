<?php

/**
 * Development routes
 */

use Illuminate\Support\Facades\Route;
use TromsFylkestrafikk\Siri\Http\Controllers\SiriDevController;

Route::post('dev/subscribe/ok', [SiriDevController::class, 'subscribeOk'])->name('siri.dev.subscribe.ok');
Route::post('dev/subscribe/fail', [SiriDevController::class, 'subscribeFailed'])->name('siri.dev.subscribe.fail');
Route::get('dev/subscriptions', [SiriDevController::class, 'subscriptions'])->name('siri.dev.subscriptions');
