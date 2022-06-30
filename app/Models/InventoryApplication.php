<?php

namespace App\Models;

use \DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryApplication extends Model
{
    use SoftDeletes;
    use HasFactory;

    public $table = 'inventory_applications';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'inventory_id',
        'application_product_id',
        'prepared',
        'accepted',
        'declined',
        'status',
        'reason',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function inventory() {
        return $this->belongsTo(Inventory::class);
    }

    public function applicationProduct() {
        return $this->belongsTo(ApplicationProduct::class);
    }
}
