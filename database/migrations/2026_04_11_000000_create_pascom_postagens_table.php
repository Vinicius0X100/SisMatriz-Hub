<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('pascom_postagens')) {
            Schema::create('pascom_postagens', function (Blueprint $table) {
                $table->id();
                $table->date('data');
                $table->time('horario');
                $table->string('celebrante');
                $table->text('descricao')->nullable();
                $table->unsignedBigInteger('comunidade_id'); // ent_id da table entidades
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('paroquia_id')->nullable(); // Para multi-tenancy se aplicável
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pascom_postagens_arquivos')) {
            Schema::create('pascom_postagens_arquivos', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('postagem_id');
                $table->string('filename');
                $table->string('original_name');
                $table->string('type'); // image ou video
                $table->unsignedBigInteger('size'); // tamanho em bytes
                $table->timestamps();

                $table->foreign('postagem_id')->references('id')->on('pascom_postagens')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('pascom_postagens_arquivos');
        Schema::dropIfExists('pascom_postagens');
    }
};
