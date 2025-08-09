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
        $year = $request->input('year', 2025); // デフォルトは2025年

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

        // 選択年に絞り込み（due_dateがその年 or due_dateがnullのものも常に表示）
        $filteredTasks = $allTasks->filter(function ($task) use ($year) {
            $isInYear = is_null($task->due_date) || $task->due_date->year == $year;
            $isNotCompleted = $task->status !== 'completed';
            return $isInYear && $isNotCompleted;
        })->sortBy(function ($task) {
            return $task->due_date ?? now()->addYears(100);
        });

        return view('task.task', [
            'user' => $user,
            'groups' => $groups,
            'allPersonalTasks' => $filteredTasks,
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
        $request->validate([
            'task_type_combined' => 'required|string', // "personal" or "group_{id}"
            'task_name'          => 'required|string|max:255',
            'start_date'         => 'nullable|date',
            'due_date'           => 'nullable|date|after_or_equal:start_date',
            'description'        => 'nullable|string',
            'image'              => 'nullable|image|max:2048',
            // 主担当を1人だけ指定したい場合（任意）
            'assignee_id'        => 'nullable|exists:users,id',
        ]);

        $user = Auth::user();

        $task = new Task();
        $task->task_name   = $request->task_name;
        $task->start_date  = $request->start_date;
        $task->due_date    = $request->due_date;
        $task->description = $request->description;
        $task->created_by  = $user->id;
        $task->status      = 'not_started';

        // 個人 or グループ判定
        $typeValue = $request->task_type_combined;

        if ($typeValue === 'personal') {
            // 個人タスク：グループなし、担当は自分
            $task->group_id    = null;
            $task->assignee_id = $user->id;

        } elseif (Str::startsWith($typeValue, 'group_')) {
            $groupId = (int) Str::after($typeValue, 'group_');

            // ログインユーザーがそのグループに属しているかチェック
            if (!$user->groups->pluck('id')->contains($groupId)) {
                return back()->withErrors(['task_type_combined' => '不正なグループが選択されました。']);
            }

            $task->group_id = $groupId;

            // 主担当（任意）：フォームで assignee_id が来ていれば採用、なければ共有（null）
            $assigneeId = $request->input('assignee_id'); // 1人だけ
            // ついでに「その人がグループメンバーか」を軽くチェック
            if ($assigneeId && Group::find($groupId)?->users->pluck('id')->contains((int)$assigneeId)) {
                $task->assignee_id = (int) $assigneeId;
            } else {
                $task->assignee_id = null; // 担当者なし＝共有
            }
        }

        $task->save(); // ← ここでID確定

        // 添付ファイル
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $path = $file->store('task_files', 'public');

            $task->attachments()->create([
                'file_path'     => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type'     => $file->getMimeType(),
                'file_size'     => $file->getSize(),
                'type'          => Str::startsWith($file->getMimeType(), 'image') ? 'image' : 'file',
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

        $groupTasks = collect();
        $selectedGroup = null;
        $groupMembers = collect();
        $inviteCandidates = collect();

        if ($selectedGroupId && $groups->pluck('id')->contains((int)$selectedGroupId)) {
            $selectedGroup = Group::find($selectedGroupId);

            $groupTasks = Task::where('group_id', $selectedGroupId)
                ->where('status', '!=', 'completed')
                ->with('assignedUsers')
                ->get()
                ->filter(fn($task) => is_null($task->due_date) || $task->due_date->year == $year)
                ->sortBy(fn($task) => $task->due_date ?? now()->addYears(100));

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
            'groups' => $groups,
            'selectedGroupId' => $selectedGroupId,
            'groupTasks' => $groupTasks,
            'year' => $year,
            'selectedGroup' => $selectedGroup,
            'groupMembers' => $groupMembers,
            'inviteCandidates' => $inviteCandidates,
            'pendingInvitedUserIds' => $pendingInvitedUserIds,
            'pendingInvitedUsers' => $pendingInvitedUsers, // ✅ これを追加
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

