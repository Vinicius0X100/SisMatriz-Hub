<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('processos_tramitacoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('processo_id');
            $table->unsignedInteger('paroquia_id');
            $table->unsignedBigInteger('de_user_id');
            $table->string('de_cargo_label', 255)->comment('Label legível do cargo de quem tramitou');
            $table->unsignedBigInteger('para_user_id')->nullable()->comment('Pessoa específica que receberá');
            $table->string('para_grupo', 100)->nullable()->comment('Slug do grupo pastoral que receberá');
            $table->text('descricao')->nullable()->comment('Andamento / observações da tramitação');
            $table->tinyInteger('status_processo')->default(1)->comment('Status do processo neste tramite');
            $table->unsignedBigInteger('mencao_tramitacao_id')->nullable()->comment('Tramitação mencionada');
            $table->tinyInteger('tipo')->default(0)->comment('0=normal, 1=abertura, 2=menção');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processos_tramitacoes');
    }
};
