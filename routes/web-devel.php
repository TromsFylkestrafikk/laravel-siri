<?php

/**
 * Development routes
 */

use Illuminate\Support\Facades\Route;
use TromsFylkestrafikk\Siri\Http\Controllers\DevelEmulateClientController;

Route::resource('devel/consume', DevelEmulateClientController::class)->only(['create', 'store']);
