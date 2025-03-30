<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User; 
use App\Models\Group; 

class TaskController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 所属グループと、そのグループのメンバー・タスク・担当者を取得
        $groups = $user->groups()->with(['users', 'tasks.assignedUsers'])->get();

        // 自分が作成したタスクと担当タスクを取得
        $createdTasks = $user->createdTasks;
        $assignedTasks = $user->assignedTasks;

        // 自分が関与しているすべてのタスク（作成＋担当）
        $allPersonalTasks = $createdTasks->merge($assignedTasks)->unique('id');

        return view('task/top', compact('user', 'groups', 'allPersonalTasks'));
    }
}

