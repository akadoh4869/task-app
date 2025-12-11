<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Task;
use App\Models\GroupInvitation;
use App\Models\User;
use App\Models\Group;
use Carbon\Carbon;




class SettingController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // ▼ 所属グループ一覧
        $groups = $user->groups()->orderBy('created_at')->get();

        // ▼ 招待一覧（保留中）
        $pendingInvitations = GroupInvitation::where('invitee_id', $user->id)
            ->where('status', 'pending')
            ->with('group')
            ->get();

        // ▼ 所属しているスペースの数（例：3つまでしか参加できないなどのロジック用）
        $totalSpaceCount = $user->groups()->count();

        // ✅ 自分の「やるべきタスク」統合（個人 + グループアサイン）
        $personalTasks = $user->createdTasks;        // 自分の個人タスク
        $assignedTasks = $user->assignedTasks;       // グループ含むアサインタスク

        $allMyTasks = $personalTasks
            ->merge($assignedTasks)
            ->unique('id');

        // ✅ 総数・完了数・達成率
        $assignedTotal = $allMyTasks->count();
        $assignedDone  = $allMyTasks->where('status', 'completed')->count();
        $assignedRate  = $assignedTotal > 0
        ? round($assignedDone / $assignedTotal * 100)
        : 0;


        // ▼ 完了タスク表示範囲（account.index でやっていた処理をそのまま移植）
        $selected = $request->input('task_scope');
        if ($selected !== null) {
            // ドロップダウンで選ばれたらセッションに保存
            session(['selected_task_scope' => $selected]);
        } else {
            // 何も来ていないときは前回値 or デフォルト personal
            $selected = session('selected_task_scope', 'personal');
        }

        $completedTasks = collect();

        if ($selected === 'all') {
            // 個人（作成＋アサイン）＋ 所属グループ（全タスク）
            $createdTasks  = $user->createdTasks;
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
            $createdTasks  = $user->createdTasks->filter(fn($task) => $task->group_id === null);
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

        // ✅ 「完了タスクのプルダウンを選んだときは、自動で完了パネルを開きたい」
        // という用途があれば使うフラグ（いらなければ削ってOK）
        $showCompletedOverlay = false;
        if ($request->has('task_scope')) {
            $showCompletedOverlay = true;
        }

         $adminStats = null;

        if ($user && $user->is_admin) {
            $adminStats = [
                'total_users'      => User::count(),
                // 'paid_users'       => User::where('subscription_status', 1)->count(),
                'total_groups'     => Group::count(),
                'total_tasks'      => Task::count(),
                'today_tasks'      => Task::whereDate('created_at', Carbon::today())->count(),
                // 'this_week_done'   => Task::whereNotNull('completed_at')
                //                         ->whereBetween('completed_at', [
                //                             Carbon::now()->startOfWeek(),
                //                             Carbon::now()->endOfWeek(),
                //                         ])->count(),
                'pending_invites'  => GroupInvitation::where('status', 'pending')->count(),
                'recent_users'     => User::orderBy('created_at', 'desc')->take(100)->get(),
            ];
        }

        return view('setting', [
            // ★ もともとの setting.blade で使っていた変数
            'pendingInvitations'   => $pendingInvitations,
            'totalSpaceCount'      => $totalSpaceCount,

            // ★ account.blade で使っていた変数たち（いまは setting.blade + partials で使う）
            'user'                 => $user,
            'groups'               => $groups,
            'selectedScope'        => $selected,
            'completedTasks'       => $completedTasks,
            'showCompletedOverlay' => $showCompletedOverlay,
            'adminStats' => $adminStats,
            // ✅ 追加：アサインタスク統計
            'assignedTotal'        => $assignedTotal,
            'assignedDone'         => $assignedDone,
            'assignedRate'         => $assignedRate,
        ]);
    }
}
