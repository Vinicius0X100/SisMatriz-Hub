<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAccess extends Model
{
    protected $table = 'user_access';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'access_date',
        'access_time',
        'device_type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
