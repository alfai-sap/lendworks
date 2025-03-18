<?php

namespace App\Traits;

use App\Models\ActivityLog;

trait LogsAdminActivity
{
    protected function logActivity($description, $level = 'info', $type = 'admin', $properties = [])
    {
        ActivityLog::create([
            'log_type' => $type,
            'level' => $level,
            'description' => $description,
            'causer_type' => auth()->user() ? get_class(auth()->user()) : null,
            'causer_id' => auth()->id(),
            'properties' => $properties
        ]);
    }
}
