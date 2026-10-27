<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampanhaSaida extends Model
{
    use HasFactory, \App\Traits\LogsActivity;

    public string $auditModuleName = 'Campanhas - Saídas';
    public string $auditTransactionalMode = 'atomic';

    public array $auditableAttributes = [
        'campanha_id',
        'data',
        'valor',
        'categoria',
        'descricao',
    ];

    protected $table = 'campanha_saidas';

    protected $fillable = [
        'campanha_id',
        'data',
        'valor',
        'categoria',
        'descricao',
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
