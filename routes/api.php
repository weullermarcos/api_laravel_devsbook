<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/ping', function (){
    return ['pong' => true];
});

//rota a ser chamada quando o usuário não está autenticado
Route::get('/401', [AuthController::class, 'unauthorized']);

Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('auth/refresh', [AuthController::class, 'refresh'])->name('refresh');

Route::post('/user', [AuthController::class, 'create'])->name('createUser');
Route::put('/user', [UserController::class, 'update'])->name('updateUser');
Route::post('/user/avatar', [UserController::class, 'updateAvatar'])->name('updateAvatar');
//Route::post('/user/cover', [UserController::class, 'updateCover'])->name('updateCover');

//Route::get('/feed', [FeedController::class, 'read'])->name('readFeed');
//Route::get('/user/feed', [FeedController::class, 'userFeed'])->name('userFeed');
//Route::get('/user/{id}/feed', [FeedController::class, 'userFeed'])->name('userFeed');
//
//Route::get('/user', [UserController::class, 'read'])->name('readUser');
//Route::get('/user/{id}', [UserController::class, 'read'])->name('readUser');
//
//Route::post('/feed', [FeedController::class, 'create'])->name('createFeed');
//
//Route::post('/post/{id}/like', [PostController::class, 'like'])->name('like');
//Route::post('/post/{id}/comment', [PostController::class, 'comment'])->name('comment');
//
//Route::get('/search', [\App\Http\Controllers\SearchController::class, 'search'])->name('search');













