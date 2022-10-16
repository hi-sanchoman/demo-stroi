<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApplicationOpenedStatus extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'application_id',
        'user_id',
        'status',
    ];

    public function application() {
        return $this->belongsTo(Application::class);
    }
}
