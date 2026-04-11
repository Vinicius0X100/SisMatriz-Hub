<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PascomPostagemArquivo extends Model
{
    use HasFactory;

    protected $table = 'pascom_postagens_arquivos';

    protected $fillable = [
        'postagem_id',
        'filename',
        'original_name',
        'type',
        'size',
    ];

    public function postagem()
    {
        return $this->belongsTo(PascomPostagem::class, 'postagem_id');
    }
}
