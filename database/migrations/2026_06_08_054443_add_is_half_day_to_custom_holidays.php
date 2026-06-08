<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('custom_holidays', function (Blueprint $table) {
            $table->boolean('is_half_day')->default(false)->after('type');
        });
    }

    public function down()
    {
        Schema::table('custom_holidays', function (Blueprint $table) {
            $table->dropColumn('is_half_day');
        });
    }
};