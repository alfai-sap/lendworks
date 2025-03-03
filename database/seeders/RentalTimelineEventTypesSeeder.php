<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RentalTimelineEventTypesSeeder extends Seeder
{
    public function run()
    {
        $types = [
            // ...existing code...
            
            // Add return-related event types
            [
                'name' => 'Return Initiated',
                'code' => 'return_initiated',
                'description' => 'Return process has been initiated',
            ],
            [
                'name' => 'Return Scheduled',
                'code' => 'return_scheduled',
                'description' => 'Return schedule has been set',
            ],
            [
                'name' => 'Return Proof Submitted',
                'code' => 'return_proof_submitted',
                'description' => 'Return proof has been submitted',
            ],
            [
                'name' => 'Return Completed',
                'code' => 'return_completed',
                'description' => 'Return has been completed and confirmed',
            ],
        ];

        DB::table('rental_timeline_event_types')->insert($types);
    }
}
