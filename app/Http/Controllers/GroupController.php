<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;

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

}