<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeedPost extends Model
{
    use HasFactory;

    protected $table = 'feed_posts';

    protected $fillable = [
        'title',
        'legend',
        'anexo',
        'level_importance',
        'send_at',
        'device',
        'views',
        'paroquia_id',
    ];

    protected $casts = [
        'send_at' => 'datetime',
        'level_importance' => 'int',
        'device' => 'int',
        'views' => 'int',
        'paroquia_id' => 'int',
    ];
}
