<?php

use Illuminate\Support\Facades\Route;
use TromsFylkestrafikk\Siri\Http\Controllers\SiriClientController;
use TromsFylkestrafikk\Siri\Http\Controllers\SiriDebugController;

Route::post('siri/consume/{channel}/{id}', [SiriClientController::class, 'consume']);


/**
 * Debug routes
 */
Route::post('siri/debug/subscribe/ok', [SiriDebugController::class, 'subscribeOk']);
Route::post('siri/debug/subscribe/fail', [SiriDebugController::class, 'subscribeFailed']);
