<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class TaskUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // ユーザーの取得
        $hodaka = DB::table('users')->where('user_name', 'ほだか')->first();
        $yuya = DB::table('users')->where('user_name', 'ゆーや')->first();
        $hiroya = DB::table('users')->where('user_name', 'ひろや')->first();

        // タスクの取得
        $tasks = DB::table('tasks')->whereIn('task_name', [
            'Task Me作成',
            'タスク洗い出し',
            'UI作成',
            '行程作成',
            '打合せ'
        ])->get();

        // タスクごとに担当者を割り当てる
        foreach ($tasks as $task) {
            $assignedUsers = [];

            switch ($task->task_name) {
                case 'Task Me作成':
                    $assignedUsers = [$hodaka->id, $yuya->id];
                    break;

                case 'タスク洗い出し':
                    $assignedUsers = [$hiroya->id];
                    break;

                case 'UI作成':
                    $assignedUsers = [$yuya->id, $hiroya->id];
                    break;

                case '行程作成':
                    $assignedUsers = [$hodaka->id];
                    break;

                case '打合せ':
                    $assignedUsers = [$hodaka->id, $yuya->id, $hiroya->id];
                    break;
            }

            // `task_user` テーブルに担当者を登録
            foreach ($assignedUsers as $userId) {
                DB::table('task_user')->insert([
                    'task_id' => $task->id,
                    'user_id' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
