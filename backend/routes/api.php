<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\WordpressController;
 
Route::get('/plugin-info', [WordpressController::class, 'getPluginDetails']);