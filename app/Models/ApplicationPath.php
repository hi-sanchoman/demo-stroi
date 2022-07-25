<?php

namespace App\Models;

use \DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApplicationPath extends Model
{
    use SoftDeletes;
    use HasFactory;

    public $table = 'application_paths';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'position',
        'type',
        'order',
        'construction_id',
        'responsible_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function construction()
    {
        return $this->belongsTo(Construction::class, 'construction_id');
    }

    public function responsible()
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
