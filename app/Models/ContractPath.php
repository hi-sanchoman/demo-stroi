<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractPath extends Model
{
    use SoftDeletes;
    use HasFactory;

    public $table = 'contract_paths';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'position',
        'type',
        'order',
        'responsible_id',
        'is_main',
        'created_at',
        'updated_at',
        'deleted_at',
    ];


    public function responsible()
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
