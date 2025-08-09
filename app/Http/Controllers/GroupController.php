<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // ← これを忘れずに！
use App\Models\Group;
use App\Models\User;
use App\Models\GroupInvitation;



class GroupController extends Controller
{
    // グループ作成画面を表示
    public function create()
    {
        return view('group.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'group_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $group = new Group();
        $group->group_name = $validated['group_name'];
        $group->description = $validated['description'] ?? null;
        $group->owner_id = auth()->id(); // ← これを追加
        $group->save();

        // 作成者をメンバーとして登録なども追加可能
        $group->users()->attach(auth()->id(), [
            'role' => 'admin',
            'approved' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('task.share', ['group_id' => $group->id])
                ->with('success', 'グループを作成しました');

    }

     public function invite(Request $request, $groupId)
    {
        $group = Group::findOrFail($groupId);

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $inviteeId = (int) $request->user_id;
        $inviterId = (int) Auth::id();

        if ($inviteeId === $inviterId) {
            return back()->with('error', '自分自身は招待できません。');
        }

        // すでに在籍なら招待しない
        $alreadyMember = DB::table('group_users')
            ->where('group_id', $groupId)
            ->where('user_id', $inviteeId)
            ->exists();
        if ($alreadyMember) {
            return back()->with('error', 'すでにグループメンバーです。');
        }

        // 既に未応答の pending があるなら touch だけ
        $pending = GroupInvitation::where('group_id', $groupId)
            ->where('invitee_id', $inviteeId)
            ->where('status', 'pending')
            ->whereNull('responded_at')
            ->first();
        if ($pending) {
            $pending->touch();
            return back()->with('success', 'すでに招待中のユーザーです（更新しました）');
        }

        // 過去の招待があれば pending に戻して再招待、無ければ新規作成
        $existing = GroupInvitation::where('group_id', $groupId)
            ->where('invitee_id', $inviteeId)
            ->first();

        if ($existing) {
            $existing->update([
                'status'       => 'pending',
                'inviter_id'   => $inviterId,
                'responded_at' => null,
                'updated_at'   => now(),
            ]);
        } else {
            GroupInvitation::create([
                'group_id'   => $groupId,
                'inviter_id' => $inviterId,
                'invitee_id' => $inviteeId,
                'status'     => 'pending',
            ]);
        }

        return back()->with('success', 'ユーザーを招待しました');
    }

     public function respond(Request $request)
    {
        $request->validate([
            'invitation_id' => 'required|exists:group_invitations,id',
            'response'      => 'required|in:accept,decline',
        ]);

        $invitation = GroupInvitation::findOrFail($request->invitation_id);
        if ($invitation->invitee_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($invitation->status !== 'pending') {
            return redirect()->route('setting.index')->with('error', 'この招待はすでに対応済みです。');
        }

        if ($request->response === 'accept') {
            // 二重挿入防止
            $existsPivot = DB::table('group_users')
                ->where('group_id', $invitation->group_id)
                ->where('user_id', auth()->id())
                ->exists();

            if (!$existsPivot) {
                DB::table('group_users')->insert([
                    'group_id'   => $invitation->group_id,
                    'user_id'    => auth()->id(),
                    'approved'   => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $invitation->update([
                'status'       => 'accepted',
                'responded_at' => now(),
            ]);
        } else {
            // 履歴不要なら削除（履歴を残したい運用なら declined に更新でも可）
            $invitation->delete();
        }

        return redirect()->route('setting.index')->with('success', '招待への対応が完了しました。');
    }

    public function leave($groupId)
    {
        $user  = Auth::user();
        $group = Group::findOrFail($groupId);

        // 1) 単独アサイン（assignee_id）を解除
        DB::table('tasks')
            ->where('group_id', $group->id)
            ->where('assignee_id', $user->id)
            ->update(['assignee_id' => null]);

        // 2) 複数アサイン（pivot: task_user）からも解除 ← これを追加！
        $taskIds = DB::table('tasks')
            ->where('group_id', $group->id)
            ->pluck('id');

        if ($taskIds->isNotEmpty()) {
            DB::table('task_user')
                ->where('user_id', $user->id)
                ->whereIn('task_id', $taskIds)
                ->delete();
        }

        // 3) グループ脱退
        $group->users()->detach($user->id);

        return redirect()->route('task.share')
            ->with('success', 'グループから脱退しました（担当からも外しました）');
    }

}
