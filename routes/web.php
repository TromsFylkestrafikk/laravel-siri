<?php

use Illuminate\Support\Facades\Route;
use TromsFylkestrafikk\Siri\Http\Controllers\SiriClientController;

Route::post('siri/consume/{channel}/{id}', [SiriClientController::class, 'consume']);
