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
        Schema::create('acao_social_equipes', function (Blueprint $table) {
            $table->id();
            $table->integer('paroquia_id')->index();
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->string('tipo', 50)->default('geral'); // 'geral', 'vicentinos', 'promocao_humana', 'pastoral_social'
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        Schema::create('acao_social_equipe_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('equipe_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->integer('paroquia_id')->index();
            $table->string('funcao', 50)->default('agente'); // 'coordenador', 'agente'
            $table->timestamps();

            $table->unique(['equipe_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acao_social_equipe_user');
        Schema::dropIfExists('acao_social_equipes');
    }
};
