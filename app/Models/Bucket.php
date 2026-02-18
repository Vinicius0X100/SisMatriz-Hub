<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bucket extends Model
{
    use HasFactory;

    protected $table = 'buckets';

    public $timestamps = false;

    protected $fillable = [
        'rand',
        'user_id',
        'paroquia_id',
        'name',
        'tamanho',
        'regiao',
        'created_at',
        'tamanho_max',
    ];

    protected $casts = [
        'tamanho' => 'int',
        'tamanho_max' => 'int',
        'regiao' => 'int',
        'user_id' => 'int',
        'paroquia_id' => 'int',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function files()
    {
        return $this->hasMany(BucketFile::class, 'bucket_id');
    }
}

