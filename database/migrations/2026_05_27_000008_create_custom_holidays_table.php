<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_holidays', function (Blueprint $table) {
            $table->id();
            $table->string('name'); 
            $table->string('type')->default('custom'); 
            $table->boolean('is_regular')->default(false); 
            $table->boolean('is_half_day')->default(false);
            $table->boolean('is_active')->default(true);   
            $table->date('date');                          
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_holidays');
    }
};