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
        Schema::table('reservas_calendario_matrimonio', function (Blueprint $table) {
            if (!Schema::hasColumn('reservas_calendario_matrimonio', 'ent_id')) {
                $table->integer('ent_id')->nullable()->after('id');
            }
            // Alterar local para string/text e nullable
            $table->string('local')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservas_calendario_matrimonio', function (Blueprint $table) {
            if (Schema::hasColumn('reservas_calendario_matrimonio', 'ent_id')) {
                $table->dropColumn('ent_id');
            }
            // Reverter local para integer (cuidado: pode perder dados de texto)
            $table->integer('local')->nullable()->change();
        });
    }
};
