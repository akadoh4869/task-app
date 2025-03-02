<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;  
use Illuminate\Database\Seeder;

class TasksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $group = DB::table('groups')->where('group_name', 'Task Me')->first();
        $hodaka = DB::table('users')->where('user_name', 'ほだか')->first();
        $yuya = DB::table('users')->where('user_name', 'ゆーや')->first();
        $hiroya = DB::table('users')->where('user_name', 'ひろや')->first();

        DB::table('tasks')->insert([
            ['group_id' => $group->id, 'created_by' => $hodaka->id,  'task_name' => 'Task Me作成', 'status' => 'in_progress', 'created_at' => now(), 'updated_at' => now()],
            ['group_id' => $group->id, 'created_by' => $hiroya->id,  'task_name' => 'タスク洗い出し', 'status' => 'completed', 'created_at' => now(), 'updated_at' => now()],
            ['group_id' => $group->id, 'created_by' => $yuya->id,  'task_name' => 'UI作成', 'status' => 'not_started', 'created_at' => now(), 'updated_at' => now()],
            ['group_id' => $group->id, 'created_by' => $hodaka->id,  'task_name' => '行程作成', 'status' => 'not_started', 'created_at' => now(), 'updated_at' => now()],
            ['group_id' => $group->id, 'created_by' => $hodaka->id,  'task_name' => '打合せ', 'status' => 'in_progress', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
