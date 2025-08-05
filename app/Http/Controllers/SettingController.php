<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\GroupInvitation;



class SettingController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 招待一覧（保留中）
        $pendingInvitations = GroupInvitation::where('invitee_id', $user->id)
            ->where('status', 'pending')
            ->with('group')
            ->get();

        // 所属しているスペースの数（例：3つまでしか参加できないなどのロジック用）
        $totalSpaceCount = $user->groups()->count();

        return view('setting', [
            'pendingInvitations' => $pendingInvitations,
            'totalSpaceCount' => $totalSpaceCount,
        ]);
    }
}
