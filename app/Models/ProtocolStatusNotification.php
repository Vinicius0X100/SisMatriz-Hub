<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProtocolStatusNotification extends Model
{
    use HasFactory;

    protected $table = 'protocol_status_notifications';

    protected $fillable = [
        'user_id',
        'protocol_id',
        'title',
        'message',
        'is_read',
    ];

    public function protocol()
    {
        return $this->belongsTo(Protocol::class, 'protocol_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
