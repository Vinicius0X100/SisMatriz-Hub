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
        Schema::create('acolitos_regras_composicao', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('paroquia_id');
            $table->unsignedBigInteger('acolito_id');
            $table->string('tipo_regra', 50); // ex: 'restricao_acompanhante', 'incompatibilidade', 'companhia_obrigatoria'
            $table->json('configuracao');
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            // Indexes for better performance during generation
            $table->index(['paroquia_id', 'acolito_id']);
            $table->index(['paroquia_id', 'tipo_regra']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acolitos_regras_composicao');
    }
};
