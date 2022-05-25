<?php

namespace App\Models;

use \DateTimeInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Application extends Model
{
    use SoftDeletes;
    use HasFactory;

    public const KIND_SELECT = [
        'acquisition_of_inventory' => 'Acquisition of inventory',
    ];

    public const STATUS_SELECT = [
        'draft'       => 'Draft',
        'in_review'   => 'In Review',
        'declined'    => 'Declined',
        'in_progress' => 'In progress',
        'completed'   => 'Completed',
    ];

    public $table = 'applications';

    protected $dates = [
        'issued_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'construction_id',
        'issued_at',
        'kind',
        'status',
        'is_urgent',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function applicationApplicationProducts()
    {
        return $this->hasMany(ApplicationProduct::class, 'application_id', 'id');
    }

    public function applicationApplicationStatuses()
    {
        return $this->hasMany(ApplicationStatus::class, 'application_id', 'id');
    }

    public function construction()
    {
        return $this->belongsTo(Construction::class, 'construction_id');
    }

    public function getIssuedAtAttribute($value)
    {
        return $value ? Carbon::createFromFormat('Y-m-d H:i:s', $value)->format(config('panel.date_format') . ' ' . config('panel.time_format')) : null;
    }

    public function setIssuedAtAttribute($value)
    {
        $this->attributes['issued_at'] = $value ? Carbon::createFromFormat(config('panel.date_format') . ' ' . config('panel.time_format'), $value)->format('Y-m-d H:i:s') : null;
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
