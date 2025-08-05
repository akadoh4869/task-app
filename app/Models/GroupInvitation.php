<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'invited_by',
        'user_id',
        'status',
    ];

    // 関連するグループ
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    // 招待を送ったユーザー
    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    // 招待されたユーザー
    public function invitee()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
