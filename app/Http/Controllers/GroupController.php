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

        $exists = DB::table('group_invitations')
            ->where('group_id', $groupId)
            ->where('invitee_id', $request->user_id)
            ->exists();

        if (!$exists) {
            DB::table('group_invitations')->insert([
                'group_id' => $groupId,
                'inviter_id' => Auth::id(),
                'invitee_id' => $request->user_id,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', 'ユーザーを招待しました');
    }

   

    public function respond(Request $request)
    {
        $request->validate([
            'invitation_id' => 'required|exists:group_invitations,id',
            'response' => 'required|in:accept,decline',
        ]);

        $invitation = GroupInvitation::find($request->invitation_id);

        if ($invitation->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($request->response === 'accept') {
            DB::table('group_users')->insert([
                'group_id' => $invitation->group_id,
                'user_id' => auth()->id(),
                'approved' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $invitation->status = 'accepted';
        } else {
            $invitation->status = 'declined';
        }

        $invitation->save();

        return redirect()->route('setting.index')->with('success', '招待への対応が完了しました。');
    }


}