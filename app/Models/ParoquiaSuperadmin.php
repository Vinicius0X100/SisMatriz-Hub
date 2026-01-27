<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParoquiaSuperadmin extends Model
{
    use HasFactory;

    protected $table = 'paroquias_superadmin';

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'diocese',
        'region',
        'status',
        'paroco',
        'foto',
    ];

    public $timestamps = false; // Assumindo que pode não ter timestamps, ajuste se necessário
}
