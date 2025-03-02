<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory, SoftDeletes;

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
        return $this->belongsToMany(User::class, 'group_users');
    }

    // グループ内のタスク
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
