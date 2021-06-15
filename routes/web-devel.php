<?php

/**
 * Development routes
 */

use Illuminate\Support\Facades\Route;
use TromsFylkestrafikk\Siri\Http\Controllers\SiriDevelController;

Route::view('devel/upload', 'upload-xml')->name('siri.upload');
Route::post('devel/upload', 'SiriController@handleXmlUpload');
