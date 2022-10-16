<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'issued_at',
        'kind',
        'status',
        'is_urgent',
        'owner_id',
        'file_contract',
        'file_signed',

        'created_at',
        'updated_at',
        'deleted_at',

        'date_start',
        'date_end',
        'price',
        'file_price',
        'address',
        'company_bin',
        'company_address',
        'company_iik',
        'company_bank',
        'company_ceo',
        'file_smeta',
        'payment_method',
        'nds',
        'warranty_amount',
        'warranty_job_period',
        'warranty_materials_period',

        'certificate',

        'deposit',
        'rent_reason',
        'rent_addons',

        'file_passport',
        'equipment_crew',
        'equipment_price_addons',
        'equipment_working_hours',
        'overrate',
        'equipment_responsible',
        'requisites',

        'notes',

        'company_name',
        'subject',
        'num',
        'avans_amount',
    ];

    public function owner() {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function contractContractStatuses()
    {
        return $this->hasMany(ContractStatus::class, 'contract_id', 'id');
    }

    public function openedStatuses()
    {
        return $this->hasMany(ContractOpenedStatus::class);
    }

    public function comments()
    {
        return $this->hasMany(ContractComment::class)->orderBy('created_at', 'desc');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
