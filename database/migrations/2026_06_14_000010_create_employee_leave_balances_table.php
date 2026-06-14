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
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->decimal('vacation_leave_balance', 8, 2)->default(0);
            $table->decimal('sick_leave_balance', 8, 2)->default(0);
            $table->integer('mandatory_leave_balance')->default(5);
            $table->integer('special_privilege_leave_balance')->default(3);
            $table->integer('special_emergency_leave_balance')->default(5);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_leave_balances');
    }
};
