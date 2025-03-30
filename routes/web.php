<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\TaskController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/top',function(){
    return view('task/top');
});
// routes/web.php

Route::get('/top', [App\Http\Controllers\TaskController::class, 'index'])->name('top');


Route::get('/detail',function(){
    return view('task/detail');
});

Route::get('/create',function(){
    return view('task/create');
});

Route::get('/setting',function(){
    return view('setting');
});

Route::get('/test',function(){
    return view('test');
});

Route::get('/test', [App\Http\Controllers\HomeController::class, 'test'])->name('test');