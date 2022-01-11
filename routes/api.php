<?php

use Illuminate\Support\Facades\Route;
use TromsFylkestrafikk\Siri\Http\Controllers\SiriClientController;

Route::post('consume/{channel}/{subscription}', [SiriClientController::class, 'consume'])
    ->name('siri.consume')
    ->where('channel', '(VM|ET|SX)')
    ->middleware('channel.subscribed');
