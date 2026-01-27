<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PinnedModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'paroquia_id',
        'module_slug',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
