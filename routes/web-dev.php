<?php

/**
 * Development routes
 */

use Illuminate\Support\Facades\Route;

Route::view('emulate', 'siri::dev.upload-xml')->name('siri.emulate');
