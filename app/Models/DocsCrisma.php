<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocsCrisma extends Model
{
    use HasFactory;

    protected $table = 'docs_check';
    public $timestamps = false;

    protected $fillable = [
        'register_id',
        'rg',
        'comprovante_residencia',
        'certidao_batismo',
        'certidao_eucaristia',
        'rg_padrinho',
        'certidao_casamento_padrinho',
        'certidao_crisma_padrinho',
    ];

    protected $casts = [
        'rg' => 'boolean',
        'comprovante_residencia' => 'boolean',
        'certidao_batismo' => 'boolean',
        'certidao_eucaristia' => 'boolean',
        'rg_padrinho' => 'boolean',
        'certidao_casamento_padrinho' => 'boolean',
        'certidao_crisma_padrinho' => 'boolean',
    ];

    public function register()
    {
        return $this->belongsTo(Register::class, 'register_id');
    }
}
