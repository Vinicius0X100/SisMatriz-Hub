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
        // First, remove any duplicates that would violate the new unique constraint
        // Keep the most recently updated one for each (paroquia_id, tipo_inscricao)
        $duplicates = \DB::table('prazos_inscricoes')
            ->select('paroquia_id', 'tipo_inscricao', \DB::raw('MAX(id) as max_id'))
            ->groupBy('paroquia_id', 'tipo_inscricao')
            ->get();

        foreach ($duplicates as $keep) {
            \DB::table('prazos_inscricoes')
                ->where('paroquia_id', $keep->paroquia_id)
                ->where('tipo_inscricao', $keep->tipo_inscricao)
                ->where('id', '!=', $keep->max_id)
                ->delete();
        }

        Schema::table('prazos_inscricoes', function (Blueprint $table) {
            // Drop the incorrect unique index if it exists
            // We use a try-catch block or just attempt to drop it
            // Assuming the name is 'unique_tipo_ativo' based on the error
            try {
                $table->dropUnique('unique_tipo_ativo');
            } catch (\Exception $e) {
                // Index might not exist or have a different name, but the error specified this name
            }
            
            // Add the correct unique index
            $table->unique(['paroquia_id', 'tipo_inscricao'], 'unique_paroquia_tipo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prazos_inscricoes', function (Blueprint $table) {
            $table->dropUnique('unique_paroquia_tipo');
            // We cannot easily restore the old bad index as it might violate data
        });
    }
};
