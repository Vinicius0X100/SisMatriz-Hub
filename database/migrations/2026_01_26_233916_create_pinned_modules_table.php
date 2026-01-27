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
        Schema::dropIfExists('pinned_modules');
        Schema::create('pinned_modules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('paroquia_id')->nullable(); // In case admin has no paroquia, or global setting
            $table->string('module_slug');
            $table->timestamps();

            // FK constraint removed due to permission issues on remote DB
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // paroquia_id relation optional depending on paroquias table structure, keeping it loose for now or adding if known
            // User said "separar por user_id e paroquia_id". I'll index them.
            $table->index(['user_id', 'paroquia_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pinned_modules');
    }
};
