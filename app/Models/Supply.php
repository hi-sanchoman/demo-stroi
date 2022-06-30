<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supply extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'constuction_id',
        'application_product_id',
        'quantity',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function construction() {
        return $this->belongsTo(Construction::class);
    }

    public function applicationProduct() {
        return $this->belongsTo(ApplicationProduct::class);
    }
}
