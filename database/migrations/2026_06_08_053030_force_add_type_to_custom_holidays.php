<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // This checks if the column is missing before trying to add it
        if (!Schema::hasColumn('custom_holidays', 'type')) {
            Schema::table('custom_holidays', function (Blueprint $table) {
                $table->string('type')->default('custom')->after('name'); 
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('custom_holidays', 'type')) {
            Schema::table('custom_holidays', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
    }
};