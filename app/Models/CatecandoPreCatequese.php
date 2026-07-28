<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatecandoPreCatequese extends Model
{
    use HasFactory;

    protected $table = 'catecandos_pre_catequese';
    protected $primaryKey = 'cr_id';
    public $timestamps = false;

    protected $fillable = [
        'turma_id',
        'register_id',
        'inscricao_id',
        'batizado',
        'is_transfered',
        'obs',
        'transfer_date',
    ];

    protected $casts = [
        'batizado'      => 'boolean',
        'is_transfered' => 'boolean',
        'transfer_date' => 'date',
    ];

    public function register()
    {
        return $this->belongsTo(Register::class, 'register_id');
    }

    public function inscricao()
    {
        return $this->belongsTo(InscricaoPreCatequese::class, 'inscricao_id');
    }

    public function turma()
    {
        return $this->belongsTo(TurmaPreCatequese::class, 'turma_id');
    }
}
