<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'group_id', 'created_by', 'task_name', 'description', 'due_date', 'status', 'deleted_by'
    ];

    protected $casts = [
        'start_date' => 'date','due_date' => 'datetime',
    ];

    // タスクを作成したユーザー
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // タスクが属するグループ
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    // タスクの担当者（多対多）
    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'task_user', 'task_id', 'user_id');
    }

    public function getStatusLabel()
    {
        $statusLabels = [
            'not_started' => '未着手',
            'in_progress' => '進行中',
            'completed' => '完了'
        ];

        return $statusLabels[$this->status] ?? '不明';
    }

    public function attachments()
    {
        return $this->hasMany(TaskAttachment::class);
    }

}
