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
                'group_id' => $group->id, 'invited_by' => $hodaka->id, 'user_id' => $user->id, 'status' => 'accepted', 'created_at' => now(), 'updated_at' => now()
            ]);
        }
    }
}
