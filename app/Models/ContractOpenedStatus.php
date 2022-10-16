<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractOpenedStatus extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'contract_id',
        'user_id',
        'status',
    ];

    public function contract() {
        return $this->belongsTo(Contract::class);
    }
}
