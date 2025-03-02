<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;  
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('users')->insert([
            ['name' => 'ほだか', 'user_name' => 'ほだか', 'email' => 'hodaka@admin.com', 'password' => Hash::make('hodaka'), 'is_admin' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ゆーや', 'user_name' => 'ゆーや', 'email' => 'yu-ya@admin.com', 'password' => Hash::make('yu-ya'), 'is_admin' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ひろや', 'user_name' => 'ひろや', 'email' => 'hiroya@admin.com', 'password' => Hash::make('hiroya'), 'is_admin' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ゆーこー', 'user_name' => 'ゆーこー', 'email' => 'yu-ko-@user.com', 'password' => Hash::make('yu-ko-'), 'is_admin' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'みう', 'user_name' => 'みう', 'email' => 'miu@user.com', 'password' => Hash::make('miu'), 'is_admin' => false, 'created_at' => now(), 'updated_at' => now()],  
            ['name' => 'ひろなお', 'user_name' => 'ひろなお', 'email' => 'hironao@user.com', 'password' => Hash::make('hironao'), 'is_admin' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
   
}
