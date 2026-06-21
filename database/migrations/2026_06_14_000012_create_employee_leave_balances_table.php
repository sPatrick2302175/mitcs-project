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
        Schema::create('employee_leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('leave_types')->restrictOnDelete();
            $table->decimal('balance', 8, 3)->default(0.000);
            $table->integer('year')->nullable()->comment('Null for cumulative leaves (VL/SL), specific year for non-cumulative leaves (SPL)'); 
            $table->timestamps();
            $table->unique(['employee_id', 'leave_type_id', 'year'], 'emp_leave_year_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_leave_balances');
    }
};