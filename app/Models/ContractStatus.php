<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractStatus extends Model
{
    use SoftDeletes;
    use HasFactory;

    public const STATUS_SELECT = [
        'incoming'  => 'Incoming',
        'In_review' => 'In Review',
        'declined'  => 'Declined',
        'accpeted'  => 'Accepted',
    ];

    public $table = 'contract_statuses';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'contract_id',
        'contract_path_id',
        'status',
        'declined_reason',
        'kind',
        'signature',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    public function contract_path()
    {
        return $this->belongsTo(ContractPath::class, 'contract_path_id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
