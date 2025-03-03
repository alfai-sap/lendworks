<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('rental_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('rental_requests', 'return_schedule_id')) {
                $table->unsignedBigInteger('return_schedule_id')->nullable();
            }
            if (!Schema::hasColumn('rental_requests', 'scheduled_return_date')) {
                $table->datetime('scheduled_return_date')->nullable();
            }
            if (!Schema::hasColumn('rental_requests', 'return_at')) {
                $table->datetime('return_at')->nullable();
            }
            if (!Schema::hasColumn('rental_requests', 'return_schedule_id')) {
                $table->foreign('return_schedule_id')->references('id')->on('lender_pickup_schedules');
            }
        });
    }

    public function down()
    {
        Schema::table('rental_requests', function (Blueprint $table) {
            $table->dropForeign(['return_schedule_id']);
            $table->dropColumn(['return_schedule_id', 'scheduled_return_date', 'return_at']);
        });
    }
};