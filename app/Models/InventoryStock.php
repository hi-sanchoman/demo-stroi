<?php

namespace App\Models;

use \DateTimeInterface;
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
        'application_equipment_id',
        'application_service_id',
        'quantity',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function applicationProduct()
    {
        return $this->belongsTo(ApplicationProduct::class);
    }

    public function applicationEquipment()
    {
        return $this->belongsTo(ApplicationEquipment::class);
    }

    public function applicationService()
    {
        return $this->belongsTo(applicationService::class);
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('d.m.Y H:i:s');
    }
}
