<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Batismo extends Model
{
    use HasFactory;

    protected $table = 'batismos';

    protected $fillable = [
        'register_id',
        'paroquia_id',
        'is_batizado',
        'data_batismo',
        'local_batismo',
        'celebrante',
        'padrinho_nome',
        'madrinha_nome',
        'livro',
        'folha',
        'registro',
        'obs',
    ];

    protected $casts = [
        'is_batizado' => 'boolean',
        'data_batismo' => 'date',
        'paroquia_id' => 'integer',
        'register_id' => 'integer',
    ];

    public function register()
    {
        return $this->belongsTo(Register::class, 'register_id');
    }

    /**
     * Sincroniza o status de batismo a partir de um registro em turma.
     * Cria o registro em Batismo se não existir.
     * 
     * @param int $registerId
     * @param bool $isBatizado
     * @return void
     */
    public static function syncFromTurma($registerId, $isBatizado)
    {
        $batismo = self::firstOrNew(['register_id' => $registerId]);
        
        if (!$batismo->exists) {
            $batismo->paroquia_id = \Illuminate\Support\Facades\Auth::user()->paroquia_id ?? 1;
        }

        // Apenas atualiza se houver mudança, para evitar overwrites desnecessários se tiver dados mais complexos depois
        // Mas como a "turma" é a fonte da verdade neste momento da sincronização:
        $batismo->is_batizado = $isBatizado;
        $batismo->save();
    }
}
