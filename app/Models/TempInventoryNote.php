<?php

namespace App\Models;

use \DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TempInventoryNote extends Model
{
    use SoftDeletes;
    use HasFactory;

    public $table = 'temp_inventory_notes';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'stock_id',
        'quantity',
        'status',
        'notes',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function sender()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function receiver()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function stock()
    {
        return $this->belongsTo(InventoryStock::class);
    }
}
