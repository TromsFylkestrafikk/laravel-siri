<?php

/**
 * Development routes
 */

use Illuminate\Support\Facades\Route;

Route::view('emulate', 'siri::devel.upload-xml')->name('siri.emulate');
