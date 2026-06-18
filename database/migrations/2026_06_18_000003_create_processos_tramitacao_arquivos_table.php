<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('processos_tramitacao_arquivos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tramitacao_id');
            $table->unsignedInteger('paroquia_id');
            $table->string('nome_original', 255);
            $table->string('caminho', 500);
            $table->string('url', 500);
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('tamanho')->nullable();
            $table->tinyInteger('privacidade')->default(0)
                ->comment('0=público, 1=somente próximo responsável, 2=somente meu grupo pastoral');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processos_tramitacao_arquivos');
    }
};
