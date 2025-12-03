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
