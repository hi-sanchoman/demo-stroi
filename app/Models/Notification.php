<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'body',
        'receiver_id',
        'action',
        'data',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
