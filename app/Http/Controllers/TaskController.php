<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Task;
use App\Models\User; 
use App\Models\Group;  
use App\Models\TaskAttachment;
use Illuminate\Support\Str;
use App\Models\GroupInvitation; // ← これを追加

class TaskController extends Controller
{
    
   public function index(Request $request)
    {
        $user = Auth::user();
        $year = (int) $request->input('year', 2025);

        $groups = $user->groups()->with(['users', 'tasks.assignedUsers'])->get();

        // 作成タスク（個人のみ or グループかつアサインされている）
        $createdTasks = $user->createdTasks->filter(function ($task) use ($user) {
            if ($task->group_id !== null) {
                return $task->assignedUsers->contains('id', $user->id);
            }
            return true;
        });

        // 担当タスク
        $assignedTasks = $user->assignedTasks;

        // 両方マージし重複除去
        $allTasks = $createdTasks->merge($assignedTasks)->unique('id');

        // 年で絞り込み（due_date null も含める）
        $inYearTasks = $allTasks->filter(function ($task) use ($year) {
            return is_null($task->due_date) || $task->due_date->year == $year;
        });

        // ✅ 未完了
        $activeTasks = $inYearTasks
            ->where('status', '!=', 'completed')
            ->sortBy(fn($task) => $task->due_date ?? now()->addYears(100));

        // ✅ 完了
        $completedTasks = $inYearTasks
            ->where('status', 'completed')
            ->sortByDesc(fn($task) => $task->updated_at ?? $task->due_date ?? now());

        return view('task.task', [
            'user' => $user,
            'groups' => $groups,
            'allPersonalTasks' => $activeTasks,      // ← これまで通り未完了（未着手/進行中）
            'completedTasks'   => $completedTasks,   // ← ★追加：完了列用
            'year' => $year,
        ]);
    }

    public function create()
    {
        $user = Auth::user(); // ← この行を追加
        $groups = $user->groups()->with('users')->get(); // 所属グループ＋そのメンバー

        return view('task.create', compact('user', 'groups'));
    }

    public function store(Request $request)
    {
        // ---- バリデーション（画像＋非画像） ----
        $request->validate([
            'task_type_combined' => 'required|string',
            'task_name'          => 'required|string|max:255',
            'start_date'         => 'nullable|date',
            'due_date'           => 'nullable|date|after_or_equal:start_date',
            'description'        => 'nullable|string',

            // 画像（複数 & 単数の後方互換）
            'images'   => 'nullable|array',
            'images.*' => 'file|mimes:jpg,jpeg,png,webp,gif,bmp,svg|max:20480',
            'image'    => 'nullable|file|mimes:jpg,jpeg,png,webp,gif,bmp,svg|max:20480',

            // 非画像（複数）
            'files'    => 'nullable|array',
            'files.*'  => 'file|max:51200', // 50MBなど任意
            'assignee_id' => 'nullable|exists:users,id',
        ]);

        $user = auth()->user();

        $task = new Task();
        $task->task_name   = $request->task_name;
        $task->start_date  = $request->start_date;
        $task->due_date    = $request->due_date;
        $task->description = $request->description;
        $task->created_by  = $user->id;
        $task->status      = 'not_started';

        // --- 個人 or グループ ---
        $typeValue = $request->task_type_combined;
        if ($typeValue === 'personal') {
            $task->group_id    = null;
            $task->assignee_id = $user->id;
        } elseif (Str::startsWith($typeValue, 'group_')) {
            $groupId = (int) Str::after($typeValue, 'group_');
            if (!$user->groups->pluck('id')->contains($groupId)) {
                return back()->withErrors(['task_type_combined' => '不正なグループが選択されました。']);
            }
            $task->group_id = $groupId;
            $assigneeId = $request->input('assignee_id');
            $task->assignee_id = ($assigneeId && Group::find($groupId)?->users->pluck('id')->contains((int)$assigneeId))
                ? (int) $assigneeId : null;
        }

        $task->save(); // ← ID確定

        // ---- ここからアップロード保存（合計5件まで） ----
        // 1) フロントから来たファイル配列を取り出し
        $images = $request->file('images', []);
        if ($request->hasFile('image')) { $images[] = $request->file('image'); } // 単数互換
        $others = $request->file('files', []);

        // 2) 合計上限チェック（サーバ側でも担保）
        $total = count($images) + count($others);
        if ($total > 5) {
            // 超過分は切り捨てる or バリデーションエラーにする（ここでは切り捨て）
            $keepImages = min(count($images), 5);
            $images = array_slice($images, 0, $keepImages);
            $others = array_slice($others, 0, 5 - $keepImages);
        }

        // 3) 画像保存
        foreach ($images as $file) {
            $path = $file->store('task_files', 'public'); // storage/app/public/task_files/...
            $task->attachments()->create([
                'file_path'     => $path,
                'original_name' => $file->getClientOriginalName() ?? $file->hashName(),
                'mime_type'     => $file->getClientMimeType(),
                'file_size'     => $file->getSize(),
                'type'          => 'image',
                // 'sort_order'  => 任意
            ]);
        }

        // 4) 非画像保存
        foreach ($others as $file) {
            $path = $file->store('task_files', 'public');
            $task->attachments()->create([
                'file_path'     => $path,
                'original_name' => $file->getClientOriginalName() ?? $file->hashName(),
                'mime_type'     => $file->getClientMimeType(),
                'file_size'     => $file->getSize(),
                'type'          => 'file',
            ]);
        }

        return redirect('/task')->with('success', 'タスクを登録しました！');
    }

    public function share(Request $request)
    {
        $user = Auth::user();
        $year = (int) $request->input('year', 2025);

        $groups = $user->groups()->orderBy('created_at')->get();
        $selectedGroupId = $request->input('group_id');

        if ($selectedGroupId === 'create') {
            return redirect()->route('group.create');
        }

        if ($selectedGroupId !== null) {
            session(['selected_group_id' => $selectedGroupId]);
        } else {
            $selectedGroupId = session('selected_group_id');
            if (!$selectedGroupId && $groups->isNotEmpty()) {
                $selectedGroupId = $groups->first()->id;
                session(['selected_group_id' => $selectedGroupId]);
            }
        }

        $groupTasks      = collect();
        $completedTasks  = collect();   // ★ 追加：完了タスク用
        $selectedGroup   = null;
        $groupMembers    = collect();
        $inviteCandidates = collect();

        if ($selectedGroupId && $groups->pluck('id')->contains((int)$selectedGroupId)) {
            $selectedGroup = Group::find($selectedGroupId);

            // ▼ 未完了タスク
            $groupTasks = Task::where('group_id', $selectedGroupId)
            ->with('assignedUsers')
            ->get()
            ->filter(fn($task) => is_null($task->due_date) || $task->due_date->year == $year)
            ->sortBy(fn($task) => $task->due_date ?? now()->addYears(100));

            // ▼ 完了タスク（右側の一覧用）
            $completedTasks = Task::where('group_id', $selectedGroupId)
                ->where('status', 'completed')
                ->with('assignedUsers')
                ->get()
                ->filter(fn($task) => is_null($task->due_date) || $task->due_date->year == $year)
                // 期限の新しいもの or 更新日の新しいものを上にしたいイメージ
                ->sortByDesc(fn($task) => $task->updated_at ?? $task->due_date ?? now());

            $groupMembers = $selectedGroup->users;

            if ($request->filled('search_user')) {
                $keyword = $request->input('search_user');
                $inviteCandidates = User::where('user_name', 'like', "%$keyword%")
                    ->whereNotIn('id', $groupMembers->pluck('id'))
                    ->get();
            }
        }

        $pendingInvitedUserIds = GroupInvitation::where('group_id', $selectedGroupId)
            ->where('status', 'pending')
            ->pluck('invitee_id');

        $pendingInvitedUsers = User::whereIn('id', $pendingInvitedUserIds)->get();

        return view('task.share', [
            'groups'              => $groups,
            'selectedGroupId'     => $selectedGroupId,
            'groupTasks'          => $groupTasks,
            'completedTasks'      => $completedTasks,   // ★ ここでビューに渡す
            'year'                => $year,
            'selectedGroup'       => $selectedGroup,
            'groupMembers'        => $groupMembers,
            'inviteCandidates'    => $inviteCandidates,
            'pendingInvitedUserIds' => $pendingInvitedUserIds,
            'pendingInvitedUsers'   => $pendingInvitedUsers,
        ]);
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

       // Ajax (fetch) の場合は必ず JSON + ステータス200を返す
        if ($request->ajax()) {
            return response()->json(['message' => 'ステータスを更新しました'], 200);
        }

        // 通常リクエスト時は元のページにリダイレクト
        return back()->with('success', 'ステータスを更新しました');
    }


    public function updateDetail(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:not_started,in_progress,completed',
            'description' => 'nullable|string',
            'assigned_user_ids' => 'array',
            'assigned_user_ids.*' => 'exists:users,id',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $task = Task::with('assignedUsers')->findOrFail($id);
        $task->status = $request->status;
        $task->description = $request->description;
        $task->start_date = $request->start_date;
        $task->due_date = $request->due_date;
        $task->save();

        // 担当者の更新（グループタスクの場合のみ）
        $task->assignedUsers()->sync($request->assigned_user_ids ?? []);

        // ✅ タスクの種類に応じてリダイレクト先を分岐
        if ($task->group_id === null) {
            return redirect()->route('task.task')->with('success', 'タスクを更新しました');
        } else {
            return redirect()->route('task.share')->with('success', '共有タスクを更新しました');
        }
    }

    public function restore(Task $task)
    {
        $user = Auth::user();

        // グループタスクは所属していればOK、個人タスクは作成 or アサインされたものだけ
        $canRestore = false;

        if ($task->group_id) {
            // グループタスク → 所属しているグループのものなら復元OK
            $canRestore = $user->groups->pluck('id')->contains($task->group_id);
        } else {
            // 個人タスク → 自分が作成者であればOK（アサインは関係なし）
            // $canRestore = $task->user_id === $user->id;
            $canRestore = $task->created_by == $user->id;
        }

        if ($canRestore && $task->status === 'completed') {
            $task->status = 'in_progress'; // または未完了状態のステータス名
            $task->save();
            // 復元後、オーバーレイを開いた状態でアカウントページに戻る
            return redirect()->route('account.index')->with('open_completed_overlay', true);
        }

        return redirect()->route('account.index')->with([
        'error' => 'タスクを復元できませんでした',
        'open_completed_overlay' => true, // オーバーレイは開いたままにする
    ]);
    }

    public function bulkComplete(Request $request)
    {
        $request->validate([
            'task_ids' => 'required|array',
            'task_ids.*' => 'exists:tasks,id',
        ]);

        Task::whereIn('id', $request->task_ids)
            ->update(['status' => 'completed']);

        return back()->with('success', '選択されたタスクを完了に更新しました');
    }





    

}

