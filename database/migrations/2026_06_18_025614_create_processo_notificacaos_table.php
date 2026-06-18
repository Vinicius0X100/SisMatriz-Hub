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
        Schema::create('processo_notificacoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('Usuário que recebe a notificação');
            $table->unsignedBigInteger('processo_id');
            $table->unsignedBigInteger('tramitacao_id');
            $table->string('title');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('processo_notificacoes');
    }
};
