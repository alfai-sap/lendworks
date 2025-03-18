<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $table = 'activity_log';

    protected $fillable = [
        'log_type',
        'level',
        'description',
        'causer_type',
        'causer_id',
        'properties'
    ];

    protected $casts = [
        'properties' => 'array'
    ];

    public function causer(): MorphTo
    {
        return $this->morphTo();
    }
}
