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
        Schema::create('escalas_acolitos_regras', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ent_id'); // Comunidade
            $table->integer('dia_semana'); // 1 = Segunda, 7 = Domingo (ou conforme padrão PHP/Laravel)
            
            // Regras de quantitativo
            $table->integer('min_acolitos')->default(0);
            $table->integer('max_acolitos')->default(0);
            $table->integer('min_coroinhas')->default(0);
            $table->integer('max_coroinhas')->default(0);
            
            // Função padrão para coroinhas (opcional)
            $table->unsignedBigInteger('coroinha_funcao_id')->nullable();
            
            // Frequência máxima de missas no mês
            $table->integer('max_serves_per_month')->default(0); // 0 = Sem limite
            
            $table->unsignedBigInteger('paroquia_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('escalas_acolitos_regras');
    }
};
