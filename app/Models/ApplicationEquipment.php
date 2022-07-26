<?php

namespace App\Models;

use \DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApplicationEquipment extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $table = 'application_equipments';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'application_id',
        'equipment_id',
        'quantity',
        'prepared',
        'delivered',
        'notes',
        'is_delivered_by_us',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class, 'application_id');
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    public function offers()
    {
        return $this->hasMany(EquipmentOffer::class);
    }

    public function inventoryApplications()
    {
        return $this->hasMany(InventoryApplication::class);
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
