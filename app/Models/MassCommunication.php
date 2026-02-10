<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MassCommunication extends Model
{
    use HasFactory;

    protected $table = 'mass_communications';

    protected $fillable = [
        'sender_id',
        'recipient_id',
        'message_body',
        'status',
        'sid',
        'paroquia_id',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient()
    {
        return $this->belongsTo(Register::class, 'recipient_id');
    }
}
