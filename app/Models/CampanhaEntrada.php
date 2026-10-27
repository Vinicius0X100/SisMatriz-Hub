<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampanhaEntrada extends Model
{
    use HasFactory, \App\Traits\LogsActivity;

    public string $auditModuleName = 'Campanhas - Entradas';
    public string $auditTransactionalMode = 'atomic';

    public array $auditableAttributes = [
        'campanha_id',
        'data',
        'valor',
        'forma',
        'observacoes',
    ];

    protected $table = 'campanha_entradas';

    protected $fillable = [
        'campanha_id',
        'data',
        'valor',
        'forma',
        'observacoes',
    ];

    protected $casts = [
        'data' => 'date',
        'valor' => 'decimal:2',
    ];

    public function campanha()
    {
        return $this->belongsTo(Campanha::class);
    }
}
