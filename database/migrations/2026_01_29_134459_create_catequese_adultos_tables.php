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
        // Catequistas Adultos
        if (!Schema::hasTable('catequistas_adultos')) {
            Schema::create('catequistas_adultos', function (Blueprint $table) {
                $table->id();
                // Sem FK por restrição de permissão do BD remoto
                $table->unsignedBigInteger('register_id'); 
                $table->string('nome');
                $table->unsignedBigInteger('ent_id')->index();
                $table->integer('status')->default(1);
                $table->unsignedBigInteger('paroquia_id')->nullable()->index();
                $table->timestamps();
            });
        }

        // Turmas Adultos
        if (!Schema::hasTable('turmas_adultos')) {
            Schema::create('turmas_adultos', function (Blueprint $table) {
                $table->id();
                $table->string('turma');
                $table->unsignedBigInteger('tutor')->nullable(); // Refere a catequistas_adultos
                $table->date('inicio')->nullable();
                $table->date('termino')->nullable();
                $table->integer('status')->default(3); // 1=Não Iniciada, 2=Concluida, 3=Em catequese, 4=Cancelada
                $table->unsignedBigInteger('paroquia_id')->index();
                $table->timestamps();
            });
        }

        // Catecandos Adultos
        if (!Schema::hasTable('catecandos_adultos')) {
            Schema::create('catecandos_adultos', function (Blueprint $table) {
                $table->id('cr_id');
                // Sem FK por segurança e permissão, manteremos apenas IDs
                $table->unsignedBigInteger('turma_id');
                $table->unsignedBigInteger('register_id');
                $table->boolean('batizado')->default(false);
                $table->boolean('is_transfered')->default(false);
                $table->date('transfer_date')->nullable();
                $table->text('obs')->nullable();
            });
        }

        // Faltas Adultos
        if (!Schema::hasTable('faltas_adultos')) {
            Schema::create('faltas_adultos', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('aluno_id');
                $table->unsignedBigInteger('turma_id');
                $table->string('title')->nullable();
                $table->date('data_aula');
                $table->boolean('status')->default(0); // 0=Falta, 1=Presença
            });
        }

        // Justificativas de Falta Adultos
        if (!Schema::hasTable('faltas_justify_adultos')) {
            Schema::create('faltas_justify_adultos', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('faltas_id');
                $table->text('motivo');
                $table->string('anexo')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faltas_justify_adultos');
        Schema::dropIfExists('faltas_adultos');
        Schema::dropIfExists('catecandos_adultos');
        Schema::dropIfExists('turmas_adultos');
        Schema::dropIfExists('catequistas_adultos');
    }
};
