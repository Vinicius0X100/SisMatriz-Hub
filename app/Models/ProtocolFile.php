<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProtocolFile extends Model
{
    use HasFactory;

    protected $table = 'protocols_files';

    protected $fillable = [
        'protocol_id',
        'file_name',
    ];

    public function protocol()
    {
        return $this->belongsTo(Protocol::class, 'protocol_id');
    }
}
