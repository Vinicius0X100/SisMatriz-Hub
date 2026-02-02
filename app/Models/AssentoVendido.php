<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssentoVendido extends Model
{
    protected $table = 'assentos_vendidos';

    protected $fillable = [
        'paroquia_id',
        'onibus_id',
        'register_id',
        'passageiro_nome',
        'passageiro_rg',
        'passageiro_telefone',
        'menor',
        'responsavel_nome',
        'responsavel_rg',
        'responsavel_telefone',
        'poltrona',
        'posicao',
        'embarque_ida',
        'embarque_volta',
    ];

    protected $casts = [
        'menor' => 'boolean',
        'embarque_ida' => 'boolean',
        'embarque_volta' => 'boolean',
    ];

    public function onibus()
    {
        return $this->belongsTo(Onibus::class, 'onibus_id');
    }

    public function register()
    {
        // Assuming Register model exists in App\Models\Register or similar
        // Based on routes/web.php: Route::resource('registers', RegisterController::class);
        // I should check if Register model exists.
        return $this->belongsTo(Register::class, 'register_id');
    }
}
