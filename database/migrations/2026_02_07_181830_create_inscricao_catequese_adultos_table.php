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
        Schema::create('inscricao_catequese_adultos', function (Blueprint $table) {
            $table->id();
            $table->integer('paroquia_id');
            $table->string('nome', 255);
            $table->string('sexo', 10);
            $table->date('data_nascimento');
            $table->string('nacionalidade', 100);
            $table->string('estado', 100);
            $table->string('cpf', 14);
            $table->string('cep', 9);
            $table->string('endereco', 255);
            $table->string('numero', 20);
            $table->string('telefone1', 20);
            $table->string('telefone2', 20);
            $table->string('filiacao', 255);
            $table->string('estado_civil', 50);
            
            $table->tinyInteger('possuiBatismo')->nullable();
            $table->string('certidao_batismo', 255)->nullable();
            
            $table->tinyInteger('possuiPrimeiraComunicacao')->nullable();
            $table->string('certidao_primeira_comunhao', 255)->nullable();
            
            $table->tinyInteger('possuiMatrimonio')->nullable();
            $table->string('certidao_matrimonio', 255)->nullable();
            
            $table->tinyInteger('lgpdConsentimento')->default(1);
            
            $table->timestamp('data_inscricao')->useCurrent();
            $table->timestamp('criado_em')->nullable();
            
            $table->integer('taxaPaga')->default(0);
            $table->integer('taxa_item_id')->default(0);
            $table->string('comprovante_pagamento', 255)->nullable();
            
            $table->integer('status')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inscricao_catequese_adultos');
    }
};
