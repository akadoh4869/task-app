<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Task;
use App\Models\User; 
use App\Models\Group;  
use App\Models\TaskAttachment;
use Illuminate\Support\Str;

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
        $user = Auth::user(); // ← この行を追加
        $groups = $user->groups()->with('users')->get(); // 所属グループ＋そのメンバー

        return view('task.create', compact('user', 'groups'));
    }

    
    public function store(Request $request)
    {
        $request->validate([
            'task_type_combined' => 'required|string', // "solo" または "group_{id}"
            'task_name' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        $user = Auth::user();

        $task = new Task();
        $task->task_name = $request->task_name;
        $task->start_date = $request->start_date;
        $task->due_date = $request->due_date;
        $task->description = $request->description;
        $task->created_by = $user->id;
        $task->status = 'not_started';

        // 個人タスク or グループタスクの判定
        $typeValue = $request->task_type_combined;

        if ($typeValue === 'personal') {
            $task->group_id = null;
        } elseif (Str::startsWith($typeValue, 'group_')) {
            $groupId = (int) Str::after($typeValue, 'group_');

            // 念のため、ユーザーがそのグループに属しているか確認（セキュリティ）
            if ($user->groups->pluck('id')->contains($groupId)) {
                $task->group_id = $groupId;
            } else {
                return back()->withErrors(['task_type_combined' => '不正なグループが選択されました。']);
            }
        }

        $task->save(); // タスク保存（ID確定）

        // 担当者（assigned_user_ids[]）が送信されていたら、アサイン
        if ($request->has('assigned_user_ids') && is_array($request->assigned_user_ids)) {
            $task->assignedUsers()->sync($request->assigned_user_ids);
        } else {
            // 個人タスクの場合は作成者のみをアサイン
            $task->assignedUsers()->attach($user->id);
        }

        // 添付ファイルがある場合
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $path = $file->store('task_files', 'public');

            $task->attachments()->create([
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'type' => Str::startsWith($file->getMimeType(), 'image') ? 'image' : 'file',
            ]);
        }

        return redirect('/task')->with('success', 'タスクを登録しました！');
    }

    public function share(Request $request)
    {
        $user = Auth::user();

        // 参加順でグループを取得（created_at順に並べる）
        $groups = $user->groups()->orderBy('created_at')->get();

        // リクエストされたgroup_idを取得
        $selectedGroupId = $request->input('group_id');

        // 「グループを作る」が選択されたらリダイレクト
        if ($selectedGroupId === 'create') {
            return redirect()->route('group.create');
        }

        // リクエストに group_id が含まれている場合はセッションに保存
        if ($selectedGroupId !== null) {
            session(['selected_group_id' => $selectedGroupId]);
        } else {
            // リクエストに group_id がない（初回アクセスなど）の場合
            $selectedGroupId = session('selected_group_id');

            // セッションにもまだ値がない（本当の初回）の場合、最初のグループを使用
            if (!$selectedGroupId && $groups->isNotEmpty()) {
                $selectedGroupId = $groups->first()->id;
                session(['selected_group_id' => $selectedGroupId]); // 初回に保存
            }
        }

        // 有効な group_id のときだけタスク取得
        $groupTasks = collect();
        if ($selectedGroupId && $groups->pluck('id')->contains((int) $selectedGroupId)) {
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

