<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('faltas_acolitos')) {
            Schema::create('faltas_acolitos', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('acolito_id');
                $table->integer('paroquia_id')->index();
                $table->string('title', 255);
                $table->date('data_aula');
                $table->boolean('status')->default(0);
                $table->integer('d_id')->nullable()->index();
                $table->boolean('grave')->default(0);

                $table->index('acolito_id');
                $table->index(['acolito_id', 'data_aula']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('faltas_acolitos');
    }
};

