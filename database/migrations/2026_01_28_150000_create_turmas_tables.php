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
        // Tabela para Turmas de Crisma (nome solicitado: 'turmas')
        if (!Schema::hasTable('turmas')) {
            Schema::create('turmas', function (Blueprint $table) {
                $table->id();
                $table->string('turma');
                // tutor referencia catequistas_crisma.id (sem FK constraint por permissão)
                $table->unsignedBigInteger('tutor')->nullable();
                $table->date('inicio')->nullable();
                $table->date('termino')->nullable();
                $table->integer('alunos_qntd')->default(0);
                $table->integer('status')->default(3); // 1=Não Iniciada, 2=Concluida, 3=Em catequese, 4=Cancelada
                $table->unsignedBigInteger('paroquia_id')->index();
                $table->timestamps();
            });
        }

        // Tabela para Turmas de Primeira Eucaristia (nome alterado para: turmas_catequese)
        if (!Schema::hasTable('turmas_catequese')) {
            Schema::create('turmas_catequese', function (Blueprint $table) {
                $table->id();
                $table->string('turma');
                // tutor referencia catequistas_eucaristia.id (sem FK constraint por permissão)
                $table->unsignedBigInteger('tutor')->nullable();
                $table->date('inicio')->nullable();
                $table->date('termino')->nullable();
                $table->integer('status')->default(3); // 1=Não Iniciada, 2=Concluida, 3=Em catequese, 4=Cancelada
                $table->unsignedBigInteger('paroquia_id')->index();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turmas_catequese');
        Schema::dropIfExists('turmas');
    }
};
