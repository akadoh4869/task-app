<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User; // ユーザーモデル
use App\Models\Group; // グループモデル（仮）

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function test()
    {
        $user = Auth::user();

        // 所属グループと、そのグループのメンバー・タスク・担当者を取得
        $groups = $user->groups()->with(['users', 'tasks.assignedUsers'])->get();

        // 自分が作成したタスクと担当タスクを取得
        $createdTasks = $user->createdTasks;
        $assignedTasks = $user->assignedTasks;

        // 自分が関与しているすべてのタスク（作成＋担当）
        $allPersonalTasks = $createdTasks->merge($assignedTasks)->unique('id');

        return view('test', compact('user', 'groups', 'allPersonalTasks'));
    }


    

}
