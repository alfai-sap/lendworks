<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RentalRequest;
use App\Models\RentalRejectionReason;
use App\Models\RentalCancellationReason;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\LenderPickupSchedule;

class LenderDashboardController extends Controller
{
    public function index()
    {
        $lender = Auth::user();
        
        // Get rentals where user is the lender
        $rentals = RentalRequest::whereHas('listing', function ($query) use ($lender) {
            $query->where('user_id', $lender->id);
        })->with(['listing.images', 'renter', 'payment_request'])->get();

        // Group rentals by status
        $groupedListings = $rentals->groupBy(function ($rental) {
            // Check for paid overdue rentals first
            if ($rental->status === 'active' && $rental->end_date < now() && $rental->payment_request && $rental->payment_request->status === 'verified') {
                return 'paid_overdue';
            }
            // Check for unpaid overdue rentals
            if ($rental->status === 'active' && $rental->end_date < now()) {
                return 'overdue';
            }

            // Special handling for payments tab
            if ($rental->status === 'approved' && $rental->payment_request) {
                return 'payments';
            }

            // Special handling for to_handover tab
            if (in_array($rental->status, ['to_handover', 'pending_proof'])) {
                return 'to_handover';
            }
            
            return $rental->status;
        })->map(function ($group) {
            return $group->map(function ($rental) {
                return [
                    'listing' => $rental->listing,
                    'rental_request' => $rental
                ];
            });
        });

  
        $rentalStats = [
            'pending' => $rentals->where('status', 'pending')->count(),
            'approved' => $rentals->where('status', 'approved')
                ->filter(function ($rental) {
                    return !$rental->payment_request;
                })->count(),
            'payments' => $rentals->where('status', 'approved')
                ->filter(function ($rental) {
                    return $rental->payment_request !== null;
                })->count(),
            'to_handover' => $rentals->whereIn('status', ['to_handover', 'pending_proof'])->count(),
            'active' => $rentals->where('status', 'active')
                ->filter(function ($rental) {
                    return $rental->end_date >= now();
                })->count(),
            'overdue' => $rentals->where('status', 'active')
                ->filter(function ($rental) {
                    return $rental->end_date < now() && (!$rental->payment_request || $rental->payment_request->status !== 'verified');
                })->count(),
            'paid_overdue' => $rentals->where('status', 'active')
                ->filter(function ($rental) {
                    return $rental->end_date < now() && $rental->payment_request && $rental->payment_request->status === 'verified';
                })->count(),
            'pending_return' => $rentals->where('status', 'pending_return')->count(),
            'return_scheduled' => $rentals->where('status', 'return_scheduled')->count(),
            'pending_return_confirmation' => $rentals->where('status', 'pending_return_confirmation')->count(),
            'completed' => $rentals->where('status', 'completed')->count(),
            'rejected' => $rentals->where('status', 'rejected')->count(),
            'cancelled' => $rentals->where('status', 'cancelled')->count(),
        ];

        $rejectionReasons = RentalRejectionReason::select('id', 'label', 'code', 'description')
            ->get()
            ->map(fn($reason) => [
                'value' => (string) $reason->id,
                'label' => $reason->label,
                'code' => $reason->code,
                'description' => $reason->description
            ])
            ->values()
            ->all();

        $cancellationReasons = RentalCancellationReason::select('id', 'label', 'code', 'description')
            ->whereIn('role', ['lender', 'both'])
            ->get()
            ->map(fn($reason) => [
                'value' => (string) $reason->id,
                'label' => $reason->label,
                'code' => $reason->code,
                'description' => $reason->description
            ])
            ->values()
            ->all();

        // Get pickup schedules
        $pickupSchedules = LenderPickupSchedule::where('user_id', $lender->id)
            ->where('is_active', true)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return Inertia::render('LenderDashboard/LenderDashboard', [
            'groupedListings' => $groupedListings,
            'rentalStats' => $rentalStats,
            'rejectionReasons' => $rejectionReasons,
            'cancellationReasons' => $cancellationReasons,
            'pickupSchedules' => $pickupSchedules,
        ]);
    }
}