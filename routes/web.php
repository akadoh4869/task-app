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

Route::get('/task',function(){
    return view('task/task');
});
// routes/web.php

Route::get('/task', [App\Http\Controllers\TaskController::class, 'index'])->name('task.task');

Route::get('/create', [TaskController::class, 'create'])->name('tasks.create');


// Route::post('/tasks/store', [TaskController::class, 'store'])->name('tasks.store');
Route::post('/task', [TaskController::class, 'store'])->name('task.store');


Route::get('/detail', [TaskController::class, 'detail'])->name('tasks.detail');
Route::get('/share', [TaskController::class, 'share'])->name('tasks.share');

Route::get('/setting',function(){
    return view('setting');
});

Route::get('/test',function(){
    return view('test');
});

Route::get('/test', [App\Http\Controllers\HomeController::class, 'test'])->name('test');