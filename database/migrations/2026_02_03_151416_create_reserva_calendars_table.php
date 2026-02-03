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
        Schema::create('reservas_calendar', function (Blueprint $table) {
            $table->id();
            $table->date('data');
            $table->time('hora_inicio');
            $table->time('hora_fim');
            $table->string('descricao');
            $table->unsignedBigInteger('local')->nullable();
            $table->string('responsavel')->nullable();
            $table->text('observacoes')->nullable();
            $table->string('color')->nullable();
            $table->unsignedBigInteger('paroquia_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservas_calendar');
    }
};
