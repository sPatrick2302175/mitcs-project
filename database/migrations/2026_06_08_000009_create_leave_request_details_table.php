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
            
            // Link back to your "Master" table
            $table->foreignId('leave_request_id')
                  ->constrained('leave_requests')
                  ->cascadeOnDelete();
            
            // The specific date the employee is taking leave
            $table->date('leave_date');
            
            // Is it a whole day (1.0) or half day (0.5)?
            // Using decimal allows you to track half-days perfectly.
            $table->decimal('day_fraction', 3, 2)->default(1.00); 
            
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