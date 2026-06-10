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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('division_id')->constrained('divisions')->onDelete('cascade');
            $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');
            $table->string('employee_id_number')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_initial', 5)->nullable();
            $table->string('position');
            $table->string('position_code')->nullable();
            //position code
            //$table->integer('leave_credits')->default(15);//temporary value, can be changed later
            
            // --- INTEGRATED FROM GROUPMATE's WORK ---
            $table->decimal('vacation_leave_balance', 8, 2)->default(0);
            $table->decimal('sick_leave_balance', 8, 2)->default(0);
            $table->integer('mandatory_leave_balance')->default(5);
            $table->integer('special_privilege_leave_balance')->default(3);
            $table->integer('special_emergency_leave_balance')->default(5);
            // ----------------------------------------
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
