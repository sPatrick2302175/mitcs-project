<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g., 'VL', 'SL'
            $table->string('leave_type_name');
            $table->boolean('is_paid')->default(true);
            $table->boolean('requires_attachment')->default(false);
            // NEW CONFIGURATION COLUMNS:
            // True for VL and SL (they roll over). False for SPL, Solo Parent, etc.
            $table->boolean('is_cumulative')->default(false)->comment('Does this leave stack/roll over to the next year?');
            // True for Maternity, Paternity, Calamity, Rehab. False for VL, SL, SPL.
            $table->boolean('is_event_based')->default(false)->comment('Is it triggered strictly by specific life events?');
            // e.g., 3.0 for SPL, 7.0 for Solo Parent, 10.0 for VAWC. Null for leaves with open timelines (like Rehab).
            $table->decimal('max_days_per_year', 5, 1)->nullable()->comment('Maximum allowable days per year or event if applicable');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};