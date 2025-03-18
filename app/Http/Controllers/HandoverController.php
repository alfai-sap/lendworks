<?php

namespace App\Http\Controllers;

use App\Models\RentalRequest;
use App\Models\HandoverProof;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Traits\LogsUserActivity;

class HandoverController extends Controller
{
    use LogsUserActivity;

    public function submitHandover(Request $request, RentalRequest $rental)
    {
        // Validate that the user is the lender
        if ($rental->listing->user_id !== Auth::id()) {
            abort(403, 'You are not authorized to perform this action.');
        }

        // Validate that the rental is in the correct status
        if (!in_array($rental->status, ['to_handover', 'pending_proof'])) {
            abort(400, 'Invalid rental status for handover.');
        }

        $request->validate([
            'proof_image' => ['required', 'image', 'max:5120'], // 5MB max
        ]);

        try {
            // Store the image
            $path = $request->file('proof_image')->store('handover-proofs', 'public');

            // Create handover proof
            HandoverProof::create([
                'rental_request_id' => $rental->id,
                'type' => 'handover',
                'proof_path' => $path,
                'submitted_by' => Auth::id(),
            ]);

            // Update rental status to pending_proof and keep it visible in To Receive tab
            $rental->update(['status' => 'pending_proof']);

            // Record timeline event
            $rental->recordTimelineEvent('handover', Auth::id(), ['proof_path' => $path]);

            // Log the activity
            $this->logUserActivity(
                "Submitted handover proof for rental #{$rental->id}",
                'info',
                [
                    'rental_id' => $rental->id,
                    'listing_id' => $rental->listing_id,
                    'listing_title' => $rental->listing->title,
                    'renter_id' => $rental->renter_id,
                    'renter_name' => $rental->renter->name,
                    'proof_path' => $path
                ]
            );

            return back()->with('success', 'Handover proof submitted successfully.');
        } catch (\Exception $e) {
            // Handle the exception
            return back()->with('error', 'An error occurred while submitting the handover proof.');
        }
    }

    public function submitReceive(Request $request, RentalRequest $rental)
    {
        // Validate that the user is the renter
        if ($rental->renter_id !== Auth::id()) {
            abort(403, 'You are not authorized to perform this action.');
        }

        // Validate that the rental is in the correct status
        if (!in_array($rental->status, ['to_handover', 'pending_proof'])) {
            abort(400, 'Invalid rental status for receive.');
        }

        $request->validate([
            'proof_image' => ['required', 'image', 'max:5120'], // 5MB max
        ]);

        try {
            // Store the image
            $path = $request->file('proof_image')->store('handover-proofs', 'public');

            // Create receive proof
            HandoverProof::create([
                'rental_request_id' => $rental->id,
                'type' => 'receive',
                'proof_path' => $path,
                'submitted_by' => Auth::id(),
            ]);

            // Update rental status to active and set handover_at timestamp
            $rental->update([
                'status' => 'active',
                'handover_at' => now(),
            ]);

            // Record timeline event
            $rental->recordTimelineEvent('receive', Auth::id(), ['proof_path' => $path]);

            // Log the activity
            $this->logUserActivity(
                "Confirmed item receipt for rental #{$rental->id}",
                'info',
                [
                    'rental_id' => $rental->id,
                    'listing_id' => $rental->listing_id,
                    'listing_title' => $rental->listing->title,
                    'lender_id' => $rental->listing->user_id,
                    'lender_name' => $rental->listing->user->name,
                    'proof_path' => $path,
                    'handover_at' => now()->toDateTimeString()
                ]
            );

            return back()->with('success', 'Receive proof submitted successfully.');
        } catch (\Exception $e) {
            // Handle the exception
            return back()->with('error', 'An error occurred while submitting the receive proof.');
        }
    }
}
