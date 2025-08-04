<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Task;

class UserController extends Controller
{
    //
    public function account()
    {
        return view('users.account');
    }

    // public function index(Request $request)
    // {
    //     $user = Auth::user();

    //     // 所属グループを取得
    //     $groups = $user->groups()->orderBy('created_at')->get();

    //     // 選択された表示対象（all, personal または group_id）
    //     $selected = $request->input('task_scope');

    //     if ($selected !== null) {
    //         session(['selected_task_scope' => $selected]);
    //     } else {
    //         $selected = session('selected_task_scope', 'personal');
    //     }

    //     $completedTasks = collect();

    //     if ($selected === 'all') {
    //         // 全完了タスク（個人＋グループ）
    //         $createdTasks = $user->createdTasks;
    //         $assignedTasks = $user->assignedTasks;

    //         $completedTasks = $createdTasks
    //             ->merge($assignedTasks)
    //             ->unique('id')
    //             ->filter(fn($task) => $task->status === 'completed')
    //             ->sortByDesc('due_date');

    //     } elseif ($selected === 'personal') {
    //         // 個人タスク
    //         $createdTasks = $user->createdTasks->filter(fn($task) => $task->group_id === null);
    //         $assignedTasks = $user->assignedTasks->filter(fn($task) => $task->group_id === null);

    //         $completedTasks = $createdTasks
    //             ->merge($assignedTasks)
    //             ->unique('id')
    //             ->filter(fn($task) => $task->status === 'completed')
    //             ->sortByDesc('due_date');

    //     } elseif (is_numeric($selected)) {
    //         // 指定グループ
    //         $groupId = (int) $selected;

    //         if ($groups->pluck('id')->contains($groupId)) {
    //             $groupTasks = Task::where('group_id', $groupId)
    //                 ->with('assignedUsers')
    //                 ->get();

    //             $completedTasks = $groupTasks
    //                 ->filter(fn($task) => $task->status === 'completed' && $task->assignedUsers->contains('id', $user->id))
    //                 ->sortByDesc('due_date');
    //         }
    //     }
    //     if ($request->has('task_scope')) {
    //         session()->flash('open_completed_overlay', true); // 1リクエスト限定で表示
    //     }

    //     return view('users.account', [
    //         'user' => $user,
    //         'groups' => $groups,
    //         'selectedScope' => $selected,
    //         'completedTasks' => $completedTasks,
    //         // 'showCompletedOverlay' => $request->has('task_scope'),
    //         'showCompletedOverlay' => session('open_completed_overlay', false),
    //     ]);
    // }

    public function index(Request $request)
    {
        $user = Auth::user();

        // 所属グループ一覧
        $groups = $user->groups()->orderBy('created_at')->get();

        // 表示対象の選択
        $selected = $request->input('task_scope');
        if ($selected !== null) {
            session(['selected_task_scope' => $selected]);
        } else {
            $selected = session('selected_task_scope', 'personal');
        }

        $completedTasks = collect();

        if ($selected === 'all') {
            // 個人（作成＋アサイン）＋ 所属グループ（全タスク）
            $createdTasks = $user->createdTasks;
            $assignedTasks = $user->assignedTasks;

            $groupTasks = Task::whereIn('group_id', $groups->pluck('id'))
                ->where('status', 'completed')
                ->with('group')
                ->get();

            $completedTasks = $createdTasks
                ->merge($assignedTasks)
                ->merge($groupTasks)
                ->unique('id')
                ->filter(fn($task) => $task->status === 'completed')
                ->sortByDesc('due_date');

        } elseif ($selected === 'personal') {
            // 個人タスク（グループなし）
            $createdTasks = $user->createdTasks->filter(fn($task) => $task->group_id === null);
            $assignedTasks = $user->assignedTasks->filter(fn($task) => $task->group_id === null);

            $completedTasks = $createdTasks
                ->merge($assignedTasks)
                ->unique('id')
                ->filter(fn($task) => $task->status === 'completed')
                ->sortByDesc('due_date');

        } elseif (is_numeric($selected)) {
            // 指定グループの全完了タスク（アサイン無関係）
            $groupId = (int) $selected;

            if ($groups->pluck('id')->contains($groupId)) {
                $completedTasks = Task::where('group_id', $groupId)
                    ->where('status', 'completed')
                    ->with('group')
                    ->orderByDesc('due_date')
                    ->get();
            }
        }

        // オーバーレイの初回表示制御
        if ($request->has('task_scope')) {
            session()->flash('open_completed_overlay', true);
        }

        return view('users.account', [
            'user' => $user,
            'groups' => $groups,
            'selectedScope' => $selected,
            'completedTasks' => $completedTasks,
            'showCompletedOverlay' => session('open_completed_overlay', false),
        ]);
    }


}
