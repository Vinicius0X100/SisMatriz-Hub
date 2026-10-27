<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Protocol extends Model
{
    use HasFactory, \App\Traits\LogsActivity;

    public string $auditModuleName = 'Gestão de Protocolos';

    public array $auditableAttributes = [
        'code',
        'user_id',
        'description',
        'paroquia_id',
        'status',
        'message',
    ];

    protected $table = 'protocols_superadmin';

    protected $fillable = [
        'code',
        'user_id',
        'description',
        'paroquia_id',
        'status',
        'message',
    ];

    public function files()
    {
        return $this->hasMany(ProtocolFile::class, 'protocol_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
