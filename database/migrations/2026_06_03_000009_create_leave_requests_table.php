<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('leave_types')->restrictOnDelete();
            $table->date('date_of_filing');

            // 6.B DETAILS OF LEAVE
            $table->string('leave_detail_category')->nullable(); 
            $table->string('leave_detail_specifics')->nullable(); 
            
            // 6.C NUMBER OF WORKING DAYS APPLIED FOR
            $table->decimal('working_days_applied', 8, 1);
            $table->date('start_date');
            $table->date('end_date');
            
            // 6.D COMMUTATION
            $table->boolean('commutation_requested')->default(false);
            
            $table->enum('status', [
                'pending', 
                'recommended_for_approval', 
                'recommended_for_disapproval', 
                'approved', 
                'disapproved'
            ])->default('pending');
            
            // 7.B RECOMMENDATION (Now points to employees table)
            $table->text('recommendation_reason')->nullable();
            $table->foreignId('recommending_officer_id')->nullable()->constrained('employees')->nullOnDelete();
            
            // 7.C & 7.D FINAL ACTION (Now points to employees table)
            $table->foreignId('approving_official_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('approved_others')->nullable();
            $table->text('disapproval_reason')->nullable();
            $table->decimal('days_with_pay', 8, 1)->nullable();
            $table->decimal('days_without_pay', 8, 1)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};