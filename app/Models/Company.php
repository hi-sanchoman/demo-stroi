<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'address',
        'phone',
        'status',
        'website',
        'service',
        'responsible_id',
    ];

    
    public function responsible()
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    public function contacts()
    {
        return $this->hasMany(CompanyContact::class);
    }
    
    public function applicationOffers()
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
