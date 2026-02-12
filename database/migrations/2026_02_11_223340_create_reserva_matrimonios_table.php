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
        if (!Schema::hasTable('reservas_calendario_matrimonio')) {
            Schema::create('reservas_calendario_matrimonio', function (Blueprint $table) {
                $table->id();
                $table->string('titulo');
                $table->date('data');
                $table->time('horario');
                $table->integer('local'); // ID da comunidade
                $table->string('telefone_noivo')->nullable();
                $table->string('telefone_noiva')->nullable();
                $table->boolean('efeito_civil')->default(0);
                $table->string('color')->nullable();
                $table->integer('paroquia_id')->index();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservas_calendario_matrimonio');
    }
};
