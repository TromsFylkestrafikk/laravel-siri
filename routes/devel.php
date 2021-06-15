<?php

/**
 * Development routes
 */

use Illuminate\Support\Facades\Route;
use TromsFylkestrafikk\Siri\Http\Controllers\SiriDebugController;

Route::post('siri/devel/subscribe/ok', [SiriDebugController::class, 'subscribeOk']);
Route::post('siri/devel/subscribe/fail', [SiriDebugController::class, 'subscribeFailed']);

Route::view('/siri/devel/upload', 'upload-xml')->name('siri.upload');
Route::post('/siri/devel/upload', 'SiriController@handleXmlUpload');
