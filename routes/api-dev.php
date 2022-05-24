<?php

/**
 * Development routes
 */

use Illuminate\Support\Facades\Route;
use TromsFylkestrafikk\Siri\Http\Controllers\SiriDevController;

Route::controller(SiriDevController::class)->group(function () {
    Route::post('dev/subscribe/{version}/ok', 'subscribeOk')
        ->name('siri.dev.subscribe.ok')
        ->where('version', '[0-9\.]+')
        ->middleware('siri.version');
    Route::post('dev/subscribe/fail', 'subscribeFailed')->name('siri.dev.subscribe.fail');
    Route::get('dev/subscriptions', 'subscriptions')->name('siri.dev.subscriptions');
});
