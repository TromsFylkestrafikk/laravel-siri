<?php

/**
 * Development routes
 */

use Illuminate\Support\Facades\Route;
use TromsFylkestrafikk\Siri\Http\Controllers\DevelEmulateClientController;

Route::get('devel/consume', [DevelEmulateClientController::class, 'uploadXml'])->name('siri.devel.consume.form');
Route::post('devel/consume', [DevelEmulateClientController::class, 'submitXml'])->name('siri.devel.consume.post');
