<?php

use Illuminate\Support\Facades\Route;
use TromsFylkestrafikk\Siri\Http\Controllers\SiriClientController;
use TromsFylkestrafikk\Siri\Http\Controllers\PtSituationController;

Route::post('consume/{channel}/{subscription:subscription_ref}', [SiriClientController::class, 'consume'])
    ->name('siri.consume')
    ->where('channel', '(VM|ET|SX)')
    ->middleware('siri.channel');

Route::apiResource('pt-situation', PtSituationController::class)->only(['index', 'show']);
