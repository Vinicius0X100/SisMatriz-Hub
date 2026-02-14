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
        Schema::create('campanha_categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->unsignedBigInteger('paroquia_id')->nullable();
            $table->timestamps();
        });

        Schema::create('campanhas', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->foreignId('categoria_id')->constrained('campanha_categorias')->onDelete('cascade');
            $table->text('descricao')->nullable();
            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();
            $table->unsignedBigInteger('paroquia_id')->nullable();
            $table->string('status')->default('ativa'); // ativa, inativa, concluida
            $table->timestamps();
        });

        Schema::create('campanha_entradas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campanha_id')->constrained('campanhas')->onDelete('cascade');
            $table->date('data');
            $table->decimal('valor', 10, 2);
            $table->string('forma')->nullable(); // Dinheiro, Pix, etc.
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });

        Schema::create('campanha_saidas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campanha_id')->constrained('campanhas')->onDelete('cascade');
            $table->date('data');
            $table->decimal('valor', 10, 2);
            $table->string('categoria')->nullable(); // Material, Mão de obra, etc.
            $table->text('descricao')->nullable();
            $table->timestamps();
        });
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
