<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservaLocal extends Model
{
    use HasFactory;

    protected $table = 'reservas_locais';

    protected $fillable = [
        'name',
        'foto',
        'paroquia_id',
    ];

    public $timestamps = false; // Assuming no timestamps as per user description of columns

    public function paroquia()
    {
        return $this->belongsTo(ParoquiaSuperadmin::class, 'paroquia_id');
    }
}
