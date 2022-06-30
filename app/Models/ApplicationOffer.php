<?php

namespace App\Models;

use \DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApplicationOffer extends Model
{
    use SoftDeletes;
    use HasFactory;

    public $table = 'application_offers';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'application_product_id',        
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

    public function applicationProduct() {
        return $this->belongsTo(ApplicationProduct::class);
    } 

    public function company() {
        return $this->belongsTo(Company::class);
    } 
}
