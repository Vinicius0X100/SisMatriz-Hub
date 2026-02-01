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
        // Ensure created_at data is preserved in criado_em if it exists
        if (Schema::hasColumn('ofertas', 'created_at') && Schema::hasColumn('ofertas', 'criado_em')) {
            DB::statement("UPDATE ofertas SET criado_em = created_at WHERE criado_em IS NULL AND created_at IS NOT NULL");
        }

        Schema::table('ofertas', function (Blueprint $table) {
            if (Schema::hasColumn('ofertas', 'created_at')) {
                $table->dropColumn('created_at');
            }
            // Ensure criado_em exists (it should, but just in case)
            if (!Schema::hasColumn('ofertas', 'criado_em')) {
                $table->timestamp('criado_em')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ofertas', function (Blueprint $table) {
            if (!Schema::hasColumn('ofertas', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
        });
        
        DB::statement("UPDATE ofertas SET created_at = criado_em WHERE created_at IS NULL AND criado_em IS NOT NULL");
    }
};
