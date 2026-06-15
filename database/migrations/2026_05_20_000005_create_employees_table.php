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
            // Changed to nullable to support onDelete('set null') per target schema
            $table->foreignId('division_id')->nullable()->constrained('divisions')->onDelete('set null');
            
            // REMOVED: department_id (Successfully normalized!)
            
            $table->string('employee_id_number')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_initial', 5)->nullable();
            $table->string('position');
            $table->string('position_code')->nullable();
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