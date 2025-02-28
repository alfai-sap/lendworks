<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PickupSchedule extends Model
{
    protected $fillable = [
        'rental_request_id',
        'pickup_date',
        'pickup_time',
        'notes'
    ];

    protected $casts = [
        'pickup_date' => 'date',
        'pickup_time' => 'datetime',
    ];

    public function rental()
    {
        return $this->belongsTo(RentalRequest::class, 'rental_request_id');
    }
}
