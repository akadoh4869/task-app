<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;  
use Illuminate\Database\Seeder;

class GroupsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $hodaka = DB::table('users')->where('user_name', 'ほだか')->first();
        if ($hodaka) {
            DB::table('groups')->insert([
                'group_name' => 'Task Me', 'owner_id' => $hodaka->id, 'description' => 'タスク管理グループ', 'invite_only' => true, 'created_at' => now(), 'updated_at' => now()
            ]);
        }
    }
}
