<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Task;
use App\Models\User; 
use App\Models\Group;  
use App\Models\TaskAttachment;

class TaskController extends Controller
{
    
   public function index()
    {
        $user = Auth::user();

        // 所属グループと、そのグループのメンバー・タスク・担当者を取得
        $groups = $user->groups()->with(['users', 'tasks.assignedUsers'])->get();

        // 自分が作成したタスクのうち、
        // グループタスクはアサインされているもののみ表示、個人タスクはすべて表示
        $createdTasks = $user->createdTasks->filter(function ($task) use ($user) {
            // グループタスクの場合はアサインされているかチェック
            if ($task->group_id !== null) {
                return $task->assignedUsers->contains('id', $user->id);
            }
            // 個人タスクは表示対象
            return true;
        });

        // 自分が現在アサインされているタスク
        $assignedTasks = $user->assignedTasks;

        // 作成＋担当のタスクをマージ・重複除外・期限順に並べる
        $allPersonalTasks = $createdTasks
            ->merge($assignedTasks)
            ->unique('id')
            ->sortBy(function ($task) {
                return $task->due_date ?? now()->addYears(100);
            });

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
            'image' => 'nullable|image|max:2048',
        ]);

        $task = new Task();
        $task->task_name = $request->task_name;
        $task->start_date = $request->start_date;
        $task->due_date = $request->due_date;
        $task->description = $request->description;
        $task->created_by = Auth::id();
        $task->status = 'not_started';

        // group_id 判定
        $typeValue = $request->task_type_combined;
        if ($typeValue === 'solo') {
            $task->group_id = null;
        } elseif (str_starts_with($typeValue, 'group_')) {
            $task->group_id = (int) str_replace('group_', '', $typeValue);
        }

        $task->save(); // 先にタスク保存して ID を確定

        // 画像があれば task_attachments に保存
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $path = $file->store('task_files', 'public');

            $task->attachments()->create([
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'type' => str_starts_with($file->getMimeType(), 'image') ? 'image' : 'file',
            ]);
        }

        return redirect('/task')->with('success', 'タスクを登録しました！');
    }

    public function share(Request $request)
    {
        $user = Auth::user();

        // 所属グループを取得
        $groups = $user->groups;

        // 選択中のグループIDをリクエストから取得
        $selectedGroupId = $request->input('group_id');

        // 選択されたグループのタスクを取得（所属確認付き）
        $groupTasks = collect();
        if ($selectedGroupId && $groups->contains('id', $selectedGroupId)) {
            $groupTasks = Task::where('group_id', $selectedGroupId)
                ->orderBy('start_date')
                ->get();
        }

        return view('task.share', compact('groups', 'selectedGroupId', 'groupTasks'));
    }

    public function detail($id)
    {
        $task = Task::with(['attachments', 'group', 'assignedUsers'])->findOrFail($id);

        return view('task.detail', compact('task'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:not_started,in_progress,completed',
        ]);

        $task = Task::findOrFail($id);
        $task->status = $request->status;
        $task->save();

        return back()->with('success', 'ステータスを更新しました');
    }

    public function updateDetail(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:not_started,in_progress,completed',
            'description' => 'nullable|string',
            'assigned_user_ids' => 'array',
            'assigned_user_ids.*' => 'exists:users,id',
        ]);

        $task = Task::with('assignedUsers')->findOrFail($id);
        $task->status = $request->status;
        $task->description = $request->description;
        $task->save();

        // 担当者更新
        $task->assignedUsers()->sync($request->assigned_user_ids ?? []);

        return redirect()->back()->with('success', 'タスクを更新しました');
    }


    

}

