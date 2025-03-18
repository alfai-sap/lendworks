<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalDispute extends Model
{
    protected $fillable = [
        'rental_request_id',
        'reason',
        'description',
        'status',
        'proof_path',
        'raised_by',
        'resolved_by',
        'resolution_type',
        'verdict',
        'verdict_notes',
        'deposit_deduction',
        'deposit_deduction_reason',
        'resolved_at'
    ];

    protected $casts = [
        'resolved_at' => 'datetime'
    ];

    public function rental(): BelongsTo
    {
        return $this->belongsTo(RentalRequest::class, 'rental_request_id');
    }

    public function raisedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'raised_by');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
