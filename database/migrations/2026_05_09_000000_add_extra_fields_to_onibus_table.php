<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('onibus', function (Blueprint $blueprint) {
            $blueprint->string('motorista')->nullable()->after('telefone_responsavel');
            $blueprint->string('placa')->nullable()->after('motorista');
            $blueprint->string('empresa')->nullable()->after('placa');
        });
    }

    public function down(): void
    {
        Schema::table('onibus', function (Blueprint $blueprint) {
            $blueprint->dropColumn(['motorista', 'placa', 'empresa']);
        });
    }
};
