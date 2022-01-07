<?php

use Illuminate\Support\Facades\Route;
use TromsFylkestrafikk\Siri\Http\Controllers\SiriClientController;

Route::post('consume/{channel}/{id}', [SiriClientController::class, 'consume'])->name('siri.consume');
