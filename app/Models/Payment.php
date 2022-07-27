<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'application_id',
        'amount',
        'paid',
        'to_be_paid',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function productOffers()
    {
        return $this->hasMany(ApplicationOffer::class);
    }

    public function equipmentOffers()
    {
        return $this->hasMany(EquipmentOffer::class);
    }

    public function serviceOffers()
    {
        return $this->hasMany(ServiceOffer::class);
    }
}
