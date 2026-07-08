<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atendimento_filas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('paroquia_id');
            $table->date('data');
            $table->tinyInteger('status')->default(0); // 0=Aguardando, 1=Ativa, 2=Encerrada
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['paroquia_id', 'data']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atendimento_filas');
    }
};
