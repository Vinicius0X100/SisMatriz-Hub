<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE inscricao_taxas_config MODIFY COLUMN tipo ENUM('crisma','eucaristia','adultos','pre_catequese') NOT NULL;");
        DB::statement("ALTER TABLE prazos_inscricoes MODIFY COLUMN tipo_inscricao ENUM('crisma','eucaristia','adultos','pre_catequese') NOT NULL;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE inscricao_taxas_config MODIFY COLUMN tipo ENUM('crisma','eucaristia','adultos') NOT NULL;");
        DB::statement("ALTER TABLE prazos_inscricoes MODIFY COLUMN tipo_inscricao ENUM('crisma','eucaristia','adultos') NOT NULL;");
    }
};
