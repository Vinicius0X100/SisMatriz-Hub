<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('paroquias_ajustes')) {
            Schema::create('paroquias_ajustes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('paroquia_id');
                $table->json('secretaria_horarios')->nullable();
                $table->json('confissoes_horarios')->nullable();
                $table->boolean('adoracao_enabled')->default(false);
                $table->json('adoracao_horarios')->nullable();
                $table->timestamps();

                $table->unique('paroquia_id');
                $table->index('paroquia_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('paroquias_ajustes');
    }
};

