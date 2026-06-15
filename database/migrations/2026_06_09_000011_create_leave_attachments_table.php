<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_attachments', function (Blueprint $table) {
            $table->id();
            // Perfectly sets up cascading delete
            $table->foreignId('leave_request_id')->constrained('leave_requests')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('file_name');
            $table->timestamp('uploaded_at')->useCurrent(); // Database handles this automatically
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_attachments');
    }
};