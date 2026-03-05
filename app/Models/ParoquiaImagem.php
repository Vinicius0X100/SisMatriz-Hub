<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParoquiaImagem extends Model
{
    use HasFactory;

    protected $table = 'paroquias_imagens';

    protected $fillable = [
        'paroquia_id',
        'imagem',
        'titulo',
        'descricao',
        'tipo',
    ];

    public function paroquia()
    {
        return $this->belongsTo(ParoquiaSuperadmin::class, 'paroquia_id');
    }
}
