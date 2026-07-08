<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atendimento_fila_itens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fila_id');
            $table->unsignedBigInteger('register_id')->nullable(); // vínculo com Registro Geral (só agendados com CPF)
            $table->string('nome');
            $table->string('assunto')->nullable();
            $table->time('hora_agendada')->nullable(); // null = walk-in
            $table->tinyInteger('tipo')->default(0); // 0=Walk-in, 1=Agendado
            $table->tinyInteger('status')->default(0); // 0=Aguardando, 1=Em atendimento, 2=Atendido, 3=Ausente
            $table->string('telefone')->nullable(); // para envio de WhatsApp (agendados)
            $table->boolean('whatsapp_enviado')->default(false);
            $table->timestamps();

            $table->foreign('fila_id')->references('id')->on('atendimento_filas')->onDelete('cascade');
            $table->index(['fila_id', 'status']);
            $table->index(['fila_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atendimento_fila_itens');
    }
};
