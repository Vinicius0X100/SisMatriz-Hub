<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('faltas_justify_acolitos')) {
            Schema::create('faltas_justify_acolitos', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('faltas_id')->index();
                $table->string('motivo', 255)->nullable();
                $table->string('anexo', 255)->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('faltas_justify_acolitos');
    }
};

