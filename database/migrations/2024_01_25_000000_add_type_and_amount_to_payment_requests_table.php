<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_requests', function (Blueprint $table) {
            $table->enum('type', ['rental', 'overdue'])->default('rental')->after('rental_request_id');
            $table->integer('amount')->nullable()->after('payment_proof_path');
        });
    }

    public function down(): void
    {
        Schema::table('payment_requests', function (Blueprint $table) {
            $table->dropColumn(['type', 'amount']);
        });
    }
};
