<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entidade extends Model
{
    use HasFactory;

    protected $table = 'entidades';
    protected $primaryKey = 'ent_id';
    
    // Assumindo timestamps false conforme o padrão visto, mas se tiver created_at e updated_at pode mudar.
    // A imagem mostra created_at, então vamos tentar gerenciar manualmente ou deixar o Eloquent tentar.
    // Se só tiver created_at e não updated_at, precisamos configurar isso.
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null; // Se não tiver updated_at

    public $timestamps = true;

    protected $fillable = [
        'ent_name',
        'address',
        'paroquia_id'
    ];

    public function paroquia()
    {
        return $this->belongsTo(ParoquiaSuperadmin::class, 'paroquia_id');
    }
}
