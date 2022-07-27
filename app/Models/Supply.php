<?php

namespace App\Models;

use \DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Supply extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'construction_id',
        'application_product_id',
        'application_equipment_id',
        'application_service_id',
        'quantity',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function construction()
    {
        return $this->belongsTo(Construction::class);
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
        return $this->belongsTo(ApplicationService::class);
    }

    // public function getCreatedAtAttribute($value)
    // {
    //     return $value ? Carbon::createFromFormat('Y-m-d H:i:s', $value)->format('Y-m-d H:i:s') : null;
    // }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('d.m.Y H:i:s');
    }
}
