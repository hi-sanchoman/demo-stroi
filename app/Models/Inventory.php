<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'construction_id', 
        'application_product_id', 
        'quantity', 
        'created_at',
        'updated_at',
    ];

    public function construction() {
        return $this->belongsTo(Construction::class);
    }

    public function applicationProduct() {
        return $this->belongsTo(ApplicationProduct::class);
    }
}
