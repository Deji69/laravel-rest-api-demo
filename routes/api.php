<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\PostController;
use App\Http\Controllers\API\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::apiResource('users', UserController::class)->only(['index', 'show']);
Route::get('users/{user}/posts', [UserController::class, 'posts']);
Route::apiResource('posts', PostController::class)->only(['index', 'show']);
Route::get('posts/{post}/comments', [PostController::class, 'comments']);
