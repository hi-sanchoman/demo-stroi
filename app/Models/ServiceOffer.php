<?php

namespace App\Models;

use \DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceOffer extends Model
{
    use SoftDeletes;
    use HasFactory;

    public $table = 'service_offers';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'application_service_id',
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

    public function applicationService()
    {
        return $this->belongsTo(ApplicationService::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
