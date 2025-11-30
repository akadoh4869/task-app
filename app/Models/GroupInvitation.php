<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'inviter_id',   // ★ ここを修正
        'invitee_id',   // ★ ここを修正
        'status',
        'responded_at', // 使っているなら追加
    ];

    // 関連するグループ
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    // 招待を送ったユーザー
    public function inviter()
    {
        return $this->belongsTo(User::class, 'inviter_id'); // ★ invited_by → inviter_id
    }

    // 招待されたユーザー
    public function invitee()
    {
        return $this->belongsTo(User::class, 'invitee_id'); // ★ user_id → invitee_id
    }
}
