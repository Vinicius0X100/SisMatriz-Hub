<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSolicitation extends Model
{
    use HasFactory;

    protected $table = 'users_solicitations';
    public $timestamps = false;

    protected $fillable = [
        'requester_id',
        'receiver_id',
        'is_accepted',
        'created_at',
    ];

    protected $casts = [
        'is_accepted' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
