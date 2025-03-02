<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'user_name', 'email', 'password', 'is_admin'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_admin' => 'boolean',
    ];

    // ユーザーが作成したタスク
    public function createdTasks()
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    // ユーザーが担当するタスク（多対多）
    public function assignedTasks()
    {
        return $this->belongsToMany(Task::class, 'task_user');
    }

    // ユーザーが参加しているグループ（多対多）
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_users');
    }
}
