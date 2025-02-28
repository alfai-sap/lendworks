<?php

namespace App\Http\Controllers;

use App\Models\RentalRequest;
use App\Models\PickupSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PickupScheduleController extends Controller
{
    public function store(Request $request, RentalRequest $rental)
    {
        // Verify user is the lender
        if ($rental->listing->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'pickup_date' => 'required|date|after_or_equal:today',
            'pickup_time' => 'required',
            'notes' => 'nullable|string|max:500',
        ]);

        $schedule = $rental->pickupSchedules()->create($validated);

        return back()->with('success', 'Pickup schedule added successfully.');
    }

    public function update(Request $request, PickupSchedule $schedule)
    {
        // Verify user is the lender
        if ($schedule->rental->listing->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'pickup_date' => 'required|date|after_or_equal:today',
            'pickup_time' => 'required',
            'notes' => 'nullable|string|max:500',
        ]);

        $schedule->update($validated);

        return back()->with('success', 'Pickup schedule updated successfully.');
    }

    public function destroy(PickupSchedule $schedule)
    {
        // Verify user is the lender
        if ($schedule->rental->listing->user_id !== Auth::id()) {
            abort(403);
        }

        $schedule->delete();

        return back()->with('success', 'Pickup schedule removed successfully.');
    }
}
