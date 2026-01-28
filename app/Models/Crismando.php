<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Crismando extends Model
{
    use HasFactory;

    protected $table = 'crismandos';
    protected $primaryKey = 'cr_id';
    public $timestamps = false;

    protected $fillable = [
        'turma_id',
        'register_id',
        'batizado',
        'is_transfered',
        'transfer_date',
    ];

    protected $casts = [
        'batizado' => 'boolean',
        'is_transfered' => 'boolean',
        'transfer_date' => 'date',
    ];

    public function register()
    {
        return $this->belongsTo(Register::class, 'register_id');
    }

    public function turma()
    {
        return $this->belongsTo(TurmaCrisma::class, 'turma_id');
    }
}
