<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SacramentoRequest extends Model
{
    use HasFactory;

    protected $table = 'sacramento_requests';

    protected $fillable = [
        'paroquia_id',
        'sacramento',
        'nome_completo',
        'telefone',
        'data_nascimento',
        'nome_mae',
        'local_batismo',
        'local_celebracao',
        'data_batismo',
        'arquivos',
        'mais_detalhes',
        'finalidade',
        'nome_conjuges',
        'data_cerimonia',
        'testemunhas',
        'celebrante',
        'nome_pais',
        'data_crisma',
        'status',
    ];

    protected $casts = [
        'data_nascimento' => 'date',
        'data_batismo' => 'date',
        'data_cerimonia' => 'date',
        'data_crisma' => 'date',
    ];

    public function paroquia()
    {
        return $this->belongsTo(Entidade::class, 'paroquia_id'); // Assuming Entidade is the Paroquia model or similar
    }
}
