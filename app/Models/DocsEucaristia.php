<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocsEucaristia extends Model
{
    use HasFactory;

    protected $table = 'docs_check_comunhao';
    public $timestamps = false;

    protected $fillable = [
        'register_id',
        'rg',
        'comprovante_residencia',
        'certidao_batismo',
    ];

    protected $casts = [
        'rg' => 'boolean',
        'comprovante_residencia' => 'boolean',
        'certidao_batismo' => 'boolean',
    ];

    public function register()
    {
        return $this->belongsTo(Register::class, 'register_id');
    }
}
