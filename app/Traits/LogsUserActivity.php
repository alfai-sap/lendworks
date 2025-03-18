<?php

namespace App\Traits;

use App\Models\ActivityLog;

trait LogsUserActivity 
{
    protected function logUserActivity($description, $level = 'info', $properties = [])
    {
        ActivityLog::create([
            'log_type' => 'user',
            'level' => $level,
            'description' => $description,
            'causer_type' => auth()->user() ? get_class(auth()->user()) : null,
            'causer_id' => auth()->id(),
            'properties' => $properties
        ]);
    }
}
