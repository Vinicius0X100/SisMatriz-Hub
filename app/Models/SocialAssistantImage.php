<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialAssistantImage extends Model
{
    use HasFactory;

    protected $table = 'social_assistant_images';
    
    protected $fillable = [
        'social_assistant_id',
        'filename',
        'original_filename',
    ];

    public function socialAssistant()
    {
        return $this->belongsTo(SocialAssistant::class, 'social_assistant_id', 's_id');
    }
}
