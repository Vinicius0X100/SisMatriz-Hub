<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcolitoFuncao extends Model
{
    use HasFactory;

    protected $table = 'acolitos_funcoes';
    protected $primaryKey = 'f_id';
    public $timestamps = false;

    protected $fillable = [
        'title',
        'paroquia_id',
    ];

    public function paroquia()
    {
        return $this->belongsTo(ParoquiaSuperadmin::class, 'paroquia_id');
    }
}
