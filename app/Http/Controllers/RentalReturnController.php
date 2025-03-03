<?php

namespace App\Http\Controllers;

use App\Models\RentalRequest;
use App\Models\HandoverProof;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class RentalReturnController extends Controller
{
    public function initiateReturn(RentalRequest $rental)
    {
        if (!$rental->available_actions['canInitiateReturn']) {
            return back()->with('error', 'Cannot initiate return process at this time.');
        }

        try {
            DB::transaction(function () use ($rental) {
                $rental->update(['status' => RentalRequest::STATUS_PENDING_RETURN]);
                $rental->recordTimelineEvent('return_initiated', Auth::id());
            });

            return back()->with('success', 'Return process initiated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to initiate return process.');
        }
    }

    public function scheduleReturn(Request $request, RentalRequest $rental)
    {
        if (!$rental->available_actions['canScheduleReturn']) {
            return back()->with('error', 'Cannot schedule return at this time.');
        }

        $validated = $request->validate([
            'schedule_id' => ['required', 'exists:lender_pickup_schedules,id'],
            'return_date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        try {
            DB::transaction(function () use ($rental, $validated) {
                $rental->update([
                    'status' => RentalRequest::STATUS_RETURN_SCHEDULED,
                    'return_schedule_id' => $validated['schedule_id'],
                    'scheduled_return_date' => $validated['return_date'],
                ]);

                $rental->recordTimelineEvent('return_scheduled', Auth::id(), [
                    'schedule_id' => $validated['schedule_id'],
                    'return_date' => $validated['return_date'],
                ]);
            });

            return back()->with('success', 'Return scheduled successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to schedule return.');
        }
    }

    public function submitProof(Request $request, RentalRequest $rental)
    {
        if (!$rental->available_actions['canConfirmReturn']) {
            return back()->with('error', 'Cannot submit return proof at this time.');
        }

        $validated = $request->validate([
            'proof_image' => ['required', 'image', 'max:5120'], // 5MB max
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            DB::transaction(function () use ($rental, $validated, $request) {
                // Store the image
                $path = $request->file('proof_image')->store('return-proofs', 'public');

                // Create handover proof
                HandoverProof::create([
                    'rental_request_id' => $rental->id,
                    'type' => 'return',
                    'proof_path' => $path,
                    'notes' => $validated['notes'] ?? null,
                    'submitted_by' => Auth::id(),
                ]);

                // Update rental status
                $rental->update(['status' => RentalRequest::STATUS_RETURN_PROOF_PENDING]);

                // Record timeline event
                $rental->recordTimelineEvent('return_proof_submitted', Auth::id(), [
                    'proof_path' => $path,
                    'notes' => $validated['notes'] ?? null,
                ]);
            });

            return back()->with('success', 'Return proof submitted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to submit return proof.');
        }
    }

    public function confirmReturn(RentalRequest $rental)
    {
        if (!$rental->hasReturnProof) {
            return back()->with('error', 'Return proof must be submitted first.');
        }

        try {
            DB::transaction(function () use ($rental) {
                $rental->update([
                    'status' => RentalRequest::STATUS_COMPLETED,
                    'return_at' => now(),
                ]);

                $rental->listing->update(['is_rented' => false]);

                $rental->recordTimelineEvent('return_completed', Auth::id());
            });

            return back()->with('success', 'Return confirmed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to confirm return.');
        }
    }
}
