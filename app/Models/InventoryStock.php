<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryStock extends Model
{
    use SoftDeletes;
    use HasFactory;

    public $table = 'inventory_stocks';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'inventory_id',
        'application_product_id',
        'quantity',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
