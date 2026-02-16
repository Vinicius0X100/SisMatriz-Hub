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
        if (!Schema::hasTable('campanha_categorias')) {
            Schema::create('campanha_categorias', function (Blueprint $table) {
                $table->id();
                $table->string('nome');
                $table->unsignedBigInteger('paroquia_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('campanhas')) {
            Schema::create('campanhas', function (Blueprint $table) {
                $table->id();
                $table->string('nome');
                $table->unsignedBigInteger('categoria_id');
                $table->text('descricao')->nullable();
                $table->date('data_inicio')->nullable();
                $table->date('data_fim')->nullable();
                $table->unsignedBigInteger('paroquia_id')->nullable();
                $table->string('status')->default('ativa');
                $table->timestamps();
                $table->index('categoria_id');
            });
        }

        if (!Schema::hasTable('campanha_entradas')) {
            Schema::create('campanha_entradas', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('campanha_id');
                $table->date('data');
                $table->decimal('valor', 10, 2);
                $table->string('forma')->nullable();
                $table->text('observacoes')->nullable();
                $table->timestamps();
                $table->index('campanha_id');
            });
        }

        if (!Schema::hasTable('campanha_saidas')) {
            Schema::create('campanha_saidas', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('campanha_id');
                $table->date('data');
                $table->decimal('valor', 10, 2);
                $table->string('categoria')->nullable();
                $table->text('descricao')->nullable();
                $table->timestamps();
                $table->index('campanha_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campanha_saidas');
        Schema::dropIfExists('campanha_entradas');
        Schema::dropIfExists('campanhas');
        Schema::dropIfExists('campanha_categorias');
    }
};
