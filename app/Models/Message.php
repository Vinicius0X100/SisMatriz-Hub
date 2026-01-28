<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'messages';
    public $timestamps = false; // Only created_at exists

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
        'is_read',
        'toast_notify',
        'created_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'toast_notify' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
