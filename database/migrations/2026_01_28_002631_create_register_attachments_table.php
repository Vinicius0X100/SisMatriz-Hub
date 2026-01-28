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
        if (!Schema::hasTable('register_attachments')) {
            Schema::create('register_attachments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('register_id')->constrained('registers')->onDelete('cascade');
                $table->string('filename');
                $table->string('original_name');
                $table->string('mime_type')->nullable();
                $table->bigInteger('size_bytes')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('register_attachments');
    }
};
