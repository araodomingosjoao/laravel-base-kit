<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('user')->group(function () {
    Route::post('/', [UserController::class, 'create']);
    Route::get('/{id}', [UserController::class, 'read']);
    Route::put('/{id}', [UserController::class, 'update']);
    Route::delete('/{id}', [UserController::class, 'delete']);
    Route::get('/', [UserController::class, 'index']);
});

Route::prefix('post')->group(function () {
    Route::post('/', [PostController::class, 'create']);
    Route::get('/{id}', [PostController::class, 'read']);
    Route::put('/{id}', [PostController::class, 'update']);
    Route::delete('/{id}', [PostController::class, 'delete']);
    Route::get('/', [PostController::class, 'index']);
});

Route::prefix('comment')->group(function () {
    Route::post('/', [CommentController::class, 'create']);
    Route::get('/{id}', [CommentController::class, 'read']);
    Route::put('/{id}', [CommentController::class, 'update']);
    Route::delete('/{id}', [CommentController::class, 'delete']);
    Route::get('/', [CommentController::class, 'index']);
});
