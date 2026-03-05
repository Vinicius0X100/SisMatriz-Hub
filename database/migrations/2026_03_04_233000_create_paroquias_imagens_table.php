<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('paroquias_imagens')) {
            Schema::create('paroquias_imagens', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('paroquia_id');
                $table->string('imagem');
                $table->string('titulo');
                $table->text('descricao')->nullable();
                $table->integer('tipo')->comment('1=Poster, 2=Postagem');
                $table->timestamps();
                
                $table->index('paroquia_id');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('paroquias_imagens');
    }
};
