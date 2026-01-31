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
        Schema::create('escalas_drafts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('es_id');
            $table->unsignedBigInteger('paroquia_id');
            $table->unsignedBigInteger('user_id');
            $table->string('title');
            $table->text('payload'); // Stores the filename of the JSON draft
            $table->string('status')->default('draft');
            $table->timestamps();

            $table->index(['es_id', 'paroquia_id']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('escalas_drafts');
    }
};
