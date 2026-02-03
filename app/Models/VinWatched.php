<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VinWatched extends Model
{
    use HasFactory;

    protected $table = 'vin_watcheds';
    protected $primaryKey = 'w_id';
    public $timestamps = false; // Manually handling created_at based on image/instructions, or let Eloquent handle if standard.
    // Image shows created_at. Let's assume standard timestamps are NOT fully present (missing updated_at).
    // So we set timestamps = false and manually fill created_at or let DB default it.
    // But usually Laravel expects both. Let's set timestamps = false and manage created_at.

    protected $fillable = [
        'name',
        'address',
        'address_number',
        'month_entire',
        'description',
        'sendby',
        'ent_id',
        'kind', // 0 = Não Assistido, 1 = Assistido
        'created_at',
        'paroquia_id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'kind' => 'integer',
        'month_entire' => 'integer',
        'ent_id' => 'integer',
        'paroquia_id' => 'integer',
    ];

    public function entidade()
    {
        return $this->belongsTo(Entidade::class, 'ent_id', 'ent_id');
    }

    public function paroquia()
    {
        return $this->belongsTo(ParoquiaSuperadmin::class, 'paroquia_id');
    }
}
