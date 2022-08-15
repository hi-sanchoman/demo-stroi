<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyContact extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'firstname',
        'lastname',
        'position',
        'email',
        'phone1',
        'phone2',
        'company_id',
    ];

    public function company() {
        return $this->belongsTo(Company::class);
    }
}
