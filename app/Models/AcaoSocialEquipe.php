<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcaoSocialEquipe extends Model
{
    use HasFactory, LogsActivity;

    protected string $auditModuleName = 'Ações Sociais - Equipes';

    public function getAuditableAttributes(): array
    {
        return [
            'paroquia_id',
            'nome',
            'descricao',
            'tipo',
            'ativo',
        ];
    }

    protected $table = 'acao_social_equipes';

    protected $fillable = [
        'paroquia_id',
        'nome',
        'descricao',
        'tipo',
        'ativo',
    ];

    protected $casts = [
        'ativo'       => 'boolean',
        'paroquia_id' => 'integer',
    ];

    public function paroquia()
    {
        return $this->belongsTo(ParoquiaSuperadmin::class, 'paroquia_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'acao_social_equipe_user', 'equipe_id', 'user_id')
            ->withPivot('funcao', 'paroquia_id')
            ->withTimestamps();
    }

    public function familias()
    {
        return $this->hasMany(VicentinosRecord::class, 'equipe_id');
    }

    public function atendimentos()
    {
        return $this->hasMany(VinWatched::class, 'equipe_id');
    }

    /**
     * Verifica se a equipe é do tipo Vicentinos (SSVP)
     */
    public function isVicentinos(): bool
    {
        return strtolower($this->tipo) === 'vicentinos';
    }
}
