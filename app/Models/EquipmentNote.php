<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EquipmentNote extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'application_equipment_id',
        'hours',
        'notes',

        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
