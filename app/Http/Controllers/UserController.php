<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Task;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;


class UserController extends Controller
{
    //
    public function account()
    {
        return view('users.account');
    }

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

     public function edit()
    {
        $user = Auth::user();
        return view('users.edit', compact('user'));
    }

    // 表示名・アイコンだけ通常更新
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'   => ['required','string','max:255'],
            'avatar' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:5120'],
        ]);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->name = $validated['name'];
        $user->save();

        return back()->with('success', 'プロフィールを更新しました。');
    }

    // メール変更（現在パスワードで確認）
    public function updateEmail(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'email'            => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'current_password' => ['required','current_password'],
        ]);

        $user->email = $validated['email'];
        $user->save();

        return back()->with('success', 'メールアドレスを変更しました。');
    }

    // ユーザーネーム変更（現在パスワードで確認）
    public function updateUserName(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'user_name'        => ['required','string','max:255', Rule::unique('users','user_name')->ignore($user->id)],
            'current_password' => ['required','current_password'],
        ]);

        $user->user_name = $validated['user_name'];
        $user->save();

        return back()->with('success', 'ユーザーネームを変更しました。');
    }

    // パスワード変更（現在パスワードで確認）
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => ['required','current_password'],
            'password'         => ['required','confirmed', PasswordRule::min(8)],
        ]);

        $user->password = Hash::make($validated['password']);
        $user->save();

        return back()->with('success', 'パスワードを変更しました。');
    }

     


}
