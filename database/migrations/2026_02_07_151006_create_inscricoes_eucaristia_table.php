<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscricoes_eucaristia', function (Blueprint $table) {
            $table->id();
            $table->integer('paroquia_id')->nullable();
            $table->string('nome');
            $table->string('sexo', 50);
            $table->date('data_nascimento');
            $table->string('nacionalidade', 100);
            $table->string('estado', 100);
            $table->string('cpf', 14)->nullable();
            $table->string('cep', 9);
            $table->text('endereco');
            $table->string('numero', 20);
            $table->string('telefone1', 20);
            $table->string('telefone2', 20);
            $table->string('filiacao');
            $table->boolean('batismo')->default(0);
            $table->string('certidao_batismo')->nullable();
            $table->timestamp('criado_em')->useCurrent();
            $table->integer('taxa_item_id')->nullable();
            $table->string('comprovante_pagamento')->nullable();
            $table->integer('taxaPaga')->nullable();
            $table->integer('status')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscricoes_eucaristia');
    }
};
