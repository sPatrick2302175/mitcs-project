<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            
            // Link to the employee applying
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('date_of_filing');

            // 6.A TYPE OF LEAVE
            $table->string('leave_type'); 
            $table->string('leave_type_others')->nullable();
            
            // 6.B DETAILS OF LEAVE
            $table->string('leave_detail_category')->nullable(); 
            $table->string('leave_detail_specifics')->nullable(); 
            
            // 6.C NUMBER OF WORKING DAYS APPLIED FOR
            $table->decimal('working_days_applied', 8, 1);
            $table->date('start_date');
            $table->date('end_date');
            
            // 6.D COMMUTATION
            $table->boolean('commutation_requested')->default(false);
            
            // Tracking Application Status
            $table->enum('status', [
            'pending', 
            'recommended_for_approval', 
            'recommended_for_disapproval', 
            'approved', 
            'disapproved', 
            'cancelled'
            ])->default('pending');
            
            // 7.B RECOMMENDATION
            $table->text('recommendation_reason')->nullable();
            $table->foreignId('recommending_officer_id')->nullable()->constrained('users')->nullOnDelete();
            
            // 7.C & 7.D FINAL ACTION
            $table->decimal('days_with_pay', 8, 1)->nullable();
            $table->decimal('days_without_pay', 8, 1)->nullable();
            $table->string('approved_others')->nullable();
            $table->text('disapproval_reason')->nullable();
            $table->foreignId('approving_official_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};