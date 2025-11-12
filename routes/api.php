<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'api.check.auth'], function () {});

Route::middleware('throttle:req-limit')->get('/test-limit', function () {
    return response()->json(['status' => 'ok']);
});
