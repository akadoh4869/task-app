<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaskAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'type',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
