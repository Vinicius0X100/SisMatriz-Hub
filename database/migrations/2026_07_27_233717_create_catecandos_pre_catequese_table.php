<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catecandos_pre_catequese', function (Blueprint $table) {
            $table->id('cr_id');
            $table->unsignedBigInteger('turma_id');
            $table->unsignedBigInteger('register_id');
            $table->unsignedBigInteger('inscricao_id')->nullable();
            $table->tinyInteger('is_transfered')->default(0);
            $table->string('obs', 100)->nullable();
            $table->date('transfer_date')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catecandos_pre_catequese');
    }
};
