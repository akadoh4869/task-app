<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;  
use Illuminate\Database\Seeder;

class GroupUsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $group = DB::table('groups')->where('group_name', 'Task Me')->first();
        $users = DB::table('users')->whereIn('user_name', ['ほだか', 'ゆーや', 'ひろや'])->get();
        
        foreach ($users as $user) {
            DB::table('group_users')->insert([
                'group_id' => $group->id, 'user_id' => $user->id, 'role' => $user->user_name === 'ほだか' ? 'admin' : 'member', 'approved' => true, 'created_at' => now(), 'updated_at' => now()
            ]);
        }
    }
}
