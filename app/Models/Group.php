<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_name', 'owner_id', 'description', 'invite_only'
    ];

    protected $casts = [
        'invite_only' => 'boolean',
    ];

    // グループのオーナー（ユーザーとのリレーション）
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    // グループに所属するユーザー（多対多）
    public function users()
    {
        return $this->belongsToMany(User::class, 'group_users')
                    ->withPivot('role', 'approved')
                    ->wherePivot('approved', true); // 承認済みのユーザーのみ取得
    }

    // グループ内のタスク
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
