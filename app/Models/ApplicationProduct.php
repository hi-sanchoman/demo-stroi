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
        'quantity',
        'notes',
        'is_delivered_by_us',
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

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
