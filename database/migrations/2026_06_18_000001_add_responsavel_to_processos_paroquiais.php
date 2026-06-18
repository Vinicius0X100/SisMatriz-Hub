<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('processos_paroquiais', function (Blueprint $table) {
            $table->unsignedBigInteger('responsavel_atual_user_id')
                ->nullable()
                ->after('status')
                ->comment('Usuário responsável atual pelo processo');
        });
    }

    public function down(): void
    {
        Schema::table('processos_paroquiais', function (Blueprint $table) {
            $table->dropColumn('responsavel_atual_user_id');
        });
    }
};
