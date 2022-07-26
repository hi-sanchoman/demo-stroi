<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EquipmentOffer extends Model
{
    use SoftDeletes;
    use HasFactory;

    public $table = 'equipment_offers';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'application_equipment_id',        
        'company_id',
        'quantity',
        'price',
        'paidTotal',
        'status',
        'file',
        
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function applicationEquipment() {
        return $this->belongsTo(ApplicationEquipment::class);
    } 

    public function company() {
        return $this->belongsTo(Company::class);
    } 
}
