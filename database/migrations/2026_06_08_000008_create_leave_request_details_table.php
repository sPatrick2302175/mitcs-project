<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_request_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_request_id')->constrained('leave_requests')->cascadeOnDelete();
            $table->date('leave_date');
            $table->decimal('day_fraction', 3, 2)->default(1.00); // decimals to track half-days
            
            // Optional: If a specific day was approved with or without pay
            // (Sometimes HR approves 2 days with pay, 1 day without pay in the same request)
            $table->boolean('is_with_pay')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_request_details');
    }
};