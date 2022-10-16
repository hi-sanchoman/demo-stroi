<?php

namespace App\Models;

use \DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApplicationStatus extends Model
{
    use SoftDeletes;
    use HasFactory;

    public const STATUS_SELECT = [
        'incoming'  => 'Incoming',
        'In_review' => 'In Review',
        'declined'  => 'Declined',
        'accpeted'  => 'Accepted',
    ];

    public $table = 'application_statuses';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'deadline_at',
    ];

    protected $fillable = [
        'application_id',
        'application_path_id',
        'status',
        'declined_reason',
        'created_at',
        'updated_at',
        'deleted_at',
        'deadline_at',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class, 'application_id');
    }

    public function application_path()
    {
        return $this->belongsTo(ApplicationPath::class, 'application_path_id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
