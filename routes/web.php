<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\GroupInvitationController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

// ホーム
Route::get('/home', [HomeController::class, 'index'])->name('home');

// ==============================
// タスク関連
// ==============================

// タスク一覧（メイン）
Route::get('/task', [TaskController::class, 'index'])->name('task.task');

// タスク作成
Route::get('/create', [TaskController::class, 'create'])->name('tasks.create');
Route::post('/task', [TaskController::class, 'store'])->name('task.store');

// タスク詳細・更新
Route::get('/detail', [TaskController::class, 'detail'])->name('tasks.detail');
Route::get('/task/detail/{id}', [TaskController::class, 'detail'])->name('task.detail');
Route::patch('/task/{id}/status', [TaskController::class, 'updateStatus'])->name('task.updateStatus');
Route::patch('/task/{id}/detail', [TaskController::class, 'updateDetail'])->name('task.updateDetail');

// 完了タスクの復元
Route::post('/tasks/{task}/restore', [TaskController::class, 'restore'])->name('tasks.restore');

// ==============================
// グループ・共有関連
// ==============================

// グループ別タスク一覧
Route::get('/task/share', [TaskController::class, 'share'])->name('task.share');

// グループ作成
Route::get('/group/create', [GroupController::class, 'create'])->name('group.create');
Route::post('/groups', [GroupController::class, 'store'])->name('groups.store');

// グループ招待
Route::post('/group/{group}/invite', [GroupController::class, 'invite'])->name('group.invite');
Route::post('/invitation/respond', [GroupController::class, 'respond'])->name('invitation.respond');

// グループ離脱
Route::delete('/group/{group}/leave', [GroupController::class, 'leave'])->name('group.leave');

// ==============================
// 管理者ページ
// ==============================
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin', function () {
        return view('admin.index');
    })->name('admin.index');
});

// ==============================
// 設定ページ（今回のメイン）
// ==============================
//
// ここで SettingController@index が
//   - $user
//   - $groups
//   - $completedTasks
//   - $selectedScope
//   - $pendingInvitations / $totalSpaceCount
// などをまとめて View に渡します。
//
Route::middleware('auth')->get('/setting', [SettingController::class, 'index'])->name('setting.index');

// ==============================
// プロフィール編集系（フォーム送信先）
// ※ 画面は /setting 内の panel-profile-edit で表示
// ==============================
Route::middleware('auth')->group(function () {
    // 表示名・アイコンの更新
    Route::patch('/users', [UserController::class, 'update'])->name('users.update');

    // 個別変更（モーダルから）
    Route::patch('/users/email',    [UserController::class, 'updateEmail'])->name('users.updateEmail');
    Route::patch('/users/username', [UserController::class, 'updateUserName'])->name('users.updateUserName');
    Route::patch('/users/password', [UserController::class, 'updatePassword'])->name('users.updatePassword');
});

