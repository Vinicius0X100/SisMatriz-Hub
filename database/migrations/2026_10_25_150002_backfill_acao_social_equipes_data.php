<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('acao_social_equipes') || !Schema::hasTable('vicentinos_records')) {
            return;
        }

        // 1. Coleta todas as paróquias distintas envolvidas
        $paroquiaIds = collect();

        if (Schema::hasTable('vicentinos_records')) {
            $fromRecords = DB::table('vicentinos_records')->whereNotNull('paroquia_id')->pluck('paroquia_id');
            $paroquiaIds = $paroquiaIds->merge($fromRecords);
        }

        if (Schema::hasTable('vin_watcheds')) {
            $fromWatcheds = DB::table('vin_watcheds')->whereNotNull('paroquia_id')->pluck('paroquia_id');
            $paroquiaIds = $paroquiaIds->merge($fromWatcheds);
        }

        if (Schema::hasTable('users')) {
            $fromUsers = DB::table('users')
                ->whereNotNull('paroquia_id')
                ->where(function ($q) {
                    $q->where('rule', 'like', '%4%');
                })
                ->pluck('paroquia_id');
            $paroquiaIds = $paroquiaIds->merge($fromUsers);
        }

        $uniqueParoquiaIds = $paroquiaIds->unique()->filter()->values();

        foreach ($uniqueParoquiaIds as $paroquiaId) {
            // Verifica se a paróquia já tem ao menos uma equipe
            $equipe = DB::table('acao_social_equipes')
                ->where('paroquia_id', $paroquiaId)
                ->first();

            if (!$equipe) {
                // Criação da equipe padrão neutra conforme especificado
                $equipeId = DB::table('acao_social_equipes')->insertGetId([
                    'paroquia_id' => $paroquiaId,
                    'nome'        => 'Equipe de Ações Sociais',
                    'descricao'   => 'Equipe padrão de Ações Sociais (criada automaticamente para registros históricos). O nome e tipo podem ser ajustados a qualquer momento pelo administrador.',
                    'tipo'        => 'geral',
                    'ativo'       => true,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            } else {
                $equipeId = $equipe->id;
            }

            // Associa registros legados de famílias
            if (Schema::hasTable('vicentinos_records') && Schema::hasColumn('vicentinos_records', 'equipe_id')) {
                DB::table('vicentinos_records')
                    ->where('paroquia_id', $paroquiaId)
                    ->where(function ($q) {
                        $q->whereNull('equipe_id')->orWhere('equipe_id', 0);
                    })
                    ->update(['equipe_id' => $equipeId]);
            }

            // Associa registros legados de atendimentos/entregas
            if (Schema::hasTable('vin_watcheds') && Schema::hasColumn('vin_watcheds', 'equipe_id')) {
                DB::table('vin_watcheds')
                    ->where('paroquia_id', $paroquiaId)
                    ->where(function ($q) {
                        $q->whereNull('equipe_id')->orWhere('equipe_id', 0);
                    })
                    ->update(['equipe_id' => $equipeId]);
            }

            // Associa usuários existentes com perfil 4 da paróquia a essa equipe neutra
            if (Schema::hasTable('users') && Schema::hasTable('acao_social_equipe_user')) {
                $usersRole4 = DB::table('users')
                    ->where('paroquia_id', $paroquiaId)
                    ->where(function ($q) {
                        $q->where('rule', 'like', '%4%');
                    })
                    ->get();

                foreach ($usersRole4 as $u) {
                    $alreadyLinked = DB::table('acao_social_equipe_user')
                        ->where('equipe_id', $equipeId)
                        ->where('user_id', $u->id)
                        ->exists();

                    if (!$alreadyLinked) {
                        DB::table('acao_social_equipe_user')->insert([
                            'equipe_id'   => $equipeId,
                            'user_id'     => $u->id,
                            'paroquia_id' => $paroquiaId,
                            'funcao'      => 'agente',
                            'created_at'  => now(),
                            'updated_at'  => now(),
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reversão do backfill mantém histórico íntegro sem exclusão destrutiva
    }
};
