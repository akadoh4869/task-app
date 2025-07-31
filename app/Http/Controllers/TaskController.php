<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Task;
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

        return view('task/task', compact('user', 'groups', 'allPersonalTasks'));
    }

    public function create()
    {
        $user = Auth::user();
        $groups = $user->groups; // 所属グループ取得

        return view('task/create', compact('groups'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'task_type_combined' => 'required|string', // solo または group_xxx
            'task_name' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            // 'image' => 'nullable|image|max:2048',
        ]);
    
        $task = new Task();
        $task->task_name = $request->task_name;
        $task->start_date = $request->start_date;
        $task->due_date = $request->due_date;
        $task->description = $request->description;
        $task->created_by = Auth::id();
        $task->status = 'not_started';
    
        // ✅ group_id 判定（'solo' or 'group_3'など）
        $typeValue = $request->task_type_combined;
    
        if ($typeValue === 'solo') {
            $task->group_id = null;
        } elseif (str_starts_with($typeValue, 'group_')) {
            $task->group_id = (int) str_replace('group_', '', $typeValue);
        }
    
        // // 画像アップロード処理（必要あれば再開）
        // if ($request->hasFile('image')) {
        //     $path = $request->file('image')->store('task_images', 'public');
        //     $task->image_path = $path;
        // }
    
        $task->save();
    
        return redirect('/task')->with('success', 'タスクを登録しました！');
    }

    public function share()
    {
        
        // $user = Auth::user();

        // // 所属グループと、そのグループのメンバー・タスク・担当者を取得
        // $groups = $user->groups()->with(['users', 'tasks.assignedUsers'])->get();

        // // 自分が作成したタスクと担当タスクを取得
        // $createdTasks = $user->createdTasks;
        // $assignedTasks = $user->assignedTasks;

        // // 自分が関与しているすべてのタスク（作成＋担当）
        // $allPersonalTasks = $createdTasks->merge($assignedTasks)->unique('id');

        // return view('/task/share', compact('user', 'groups', 'allPersonalTasks'));return view('task.share');
        
        $user = Auth::user();

        // 自分が参加しているグループのタスクのみ取得
        $groupTasks = Task::whereNotNull('group_id')
            ->where(function ($query) use ($user) {
                $query->where('created_by', $user->id)
                    ->orWhereIn('group_id', $user->groups->pluck('id')); // グループ参加者として表示
            })
            ->orderBy('start_date')
            ->get();

        return view('task.share', compact('groupTasks'));
    }
    
    public function detail()
    {
        return view('task.detail');
    }

    

}

