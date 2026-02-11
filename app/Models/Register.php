<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Register extends Model
{
    use HasFactory;

    protected $table = 'registers';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'address_number',
        'cpf',
        'rg',
        'familly_qntd',
        'civil_status',
        'sexo',
        'age',
        'born_date',
        'work_state',
        'race',
        'country',
        'state',
        'city',
        'cep',
        'mother_name',
        'motherPhone',
        'father_name',
        'fatherPhone',
        'home_situation',
        'status',
        'photo',
        'paroquia_id',
        'observations',
    ];

    protected $casts = [
        'born_date' => 'date',
        'familly_qntd' => 'integer',
        'civil_status' => 'integer',
        'sexo' => 'integer',
        'age' => 'integer',
        'work_state' => 'integer',
        'race' => 'integer',
        // 'home_situation' => 'integer', // Removed cast to allow storing Bairro string
        'status' => 'integer',
        'paroquia_id' => 'integer',
    ];

    // Relacionamento com Paróquia (opcional, mas boa prática)
    public function paroquia()
    {
        return $this->belongsTo(ParoquiaSuperadmin::class, 'paroquia_id');
    }

    public function attachments()
    {
        return $this->hasMany(RegisterAttachment::class, 'register_id');
    }

    public function docsCrisma()
    {
        return $this->hasOne(DocsCrisma::class, 'register_id');
    }

    public function docsEucaristia()
    {
        return $this->hasOne(DocsEucaristia::class, 'register_id');
    }

    public function batismo()
    {
        return $this->hasOne(Batismo::class, 'register_id');
    }

    public function acolitos()
    {
        return $this->hasMany(Acolito::class, 'register_id');
    }

    public function acolitoNotes()
    {
        return $this->hasMany(AcolitoNote::class, 'send_by', 'id');
    }
}
