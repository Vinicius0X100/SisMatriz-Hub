<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faltas_pre_catequese', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('aluno_id');
            $table->unsignedBigInteger('turma_id');
            $table->string('title', 255)->nullable();
            $table->date('data_aula');
            $table->tinyInteger('status')->default(0); // 0=falta, 1=presente
            $table->string('justify', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faltas_pre_catequese');
    }
};
