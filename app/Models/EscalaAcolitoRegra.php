<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EscalaAcolitoRegra extends Model
{
    use HasFactory;

    protected $table = 'escalas_acolitos_regras';

    protected $fillable = [
        'ent_id',
        'dia_semana',
        'min_acolitos',
        'max_acolitos',
        'min_coroinhas',
        'max_coroinhas',
        'coroinha_funcao_id',
        'max_serves_per_month',
        'paroquia_id'
    ];

    public function entidade()
    {
        return $this->belongsTo(Entidade::class, 'ent_id', 'ent_id');
    }

    public function coroinhaFuncao()
    {
        return $this->belongsTo(AcolitoFuncao::class, 'coroinha_funcao_id', 'f_id');
    }
}
