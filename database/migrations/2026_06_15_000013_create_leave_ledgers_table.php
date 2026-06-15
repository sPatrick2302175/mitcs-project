<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('leave_types')->restrictOnDelete();
            $table->enum('type', ['accrual', 'deduction', 'adjustment']);
            
            $table->decimal('amount', 8, 3);
            $table->decimal('running_balance', 8, 3);
            
            // 1. Polymorphic Reference (No strict foreign key constraints)
            $table->string('reference_type')->nullable(); // e.g., 'App\Models\LeaveRequest'
            $table->unsignedBigInteger('reference_id')->nullable(); 
            
            // 2. Accountability
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason_code')->nullable(); // e.g., 'MONTHLY_ACCRUAL'
            $table->text('remarks')->nullable();
            
            // 3. Laravel Timestamps
            // We use timestamps() to generate both created_at and updated_at. 
            // Even though ledgers are append-only, Eloquent will crash on save() if updated_at is missing.
            $table->timestamps(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_ledgers');
    }
};