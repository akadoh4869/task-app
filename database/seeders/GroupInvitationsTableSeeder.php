<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;  
use Illuminate\Database\Seeder;

class GroupInvitationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $group = DB::table('groups')->where('group_name', 'Task Me')->first();
        $hodaka = DB::table('users')->where('user_name', 'ほだか')->first();
        $users = DB::table('users')->whereIn('user_name', ['ゆーや', 'ひろや'])->get();
        
        foreach ($users as $user) {
            DB::table('group_invitations')->insert([
            'group_id' => $group->id,
            'inviter_id' => $hodaka->id,   // ← 修正
            'invitee_id' => $user->id,     // ← 修正
            'status' => 'accepted',
            'created_at' => now(),
            'updated_at' => now(),
            'responded_at' => now(),       // ← 必要に応じて追加（nullable なので省略も可）
            ]);
        }

    }
}
