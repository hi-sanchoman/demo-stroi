<?php

namespace App\Models;

use \DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApplicationProduct extends Model
{
    use SoftDeletes;
    use HasFactory;

    public $table = 'application_products';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'application_id',
        'product_id',
        'product_category_id',
        'unit_id',
        'quantity',
        'prepared',
        'delivered',
        'notes',
        'is_delivered_by_us',
        'price',
        'company',
        'files',
        'accepted_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class, 'application_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function offers()
    {
        return $this->hasMany(ApplicationOffer::class);
    }

    public function inventoryApplications() 
    {
        return $this->hasMany(InventoryApplication::class);
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
