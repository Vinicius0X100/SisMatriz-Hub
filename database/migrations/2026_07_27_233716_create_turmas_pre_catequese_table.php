<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('turmas_pre_catequese', function (Blueprint $table) {
            $table->id();
            $table->string('turma', 100);
            $table->unsignedBigInteger('tutor')->nullable();
            $table->string('inicio', 77);
            $table->string('termino', 77);
            $table->integer('status')->default(0);
            $table->unsignedBigInteger('paroquia_id')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('turmas_pre_catequese');
    }
};
