<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PivotTaskUser extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'pivot_task_user';

    protected $fillable = [
        'task_id',
        'user_id',
    ];
}
