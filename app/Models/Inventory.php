<?php

namespace App\Models;

use \DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'construction_id', 
        'owner_id', 
        'created_at',
        'updated_at',
    ];

    public function construction() {
        return $this->belongsTo(Construction::class);
    }

    public function owner() {
        return $this->belongsTo(User::class);
    }
}
