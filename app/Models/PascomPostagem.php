<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PascomPostagem extends Model
{
    use HasFactory;

    protected $table = 'pascom_postagens';

    protected $fillable = [
        'data',
        'horario',
        'celebrante',
        'descricao',
        'comunidade_id',
        'user_id',
        'paroquia_id'
    ];

    protected $casts = [
        'data' => 'date',
        // horario is string or time, cast as string usually works well
    ];

    public function arquivos()
    {
        return $this->hasMany(PascomPostagemArquivo::class, 'postagem_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relacionamento com a comunidade (tabela entidades)
    // No Laravel, se a tabela não seguir padrão:
    public function comunidade()
    {
        return $this->belongsTo(Entidade::class, 'comunidade_id', 'ent_id');
    }
}
