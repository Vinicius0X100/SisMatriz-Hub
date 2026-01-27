<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegisterAttachment extends Model
{
    use HasFactory;

    protected $table = 'register_attachments';
    public $timestamps = false; // Based on screenshot, there is created_at but maybe not updated_at. 
                                // Actually screenshot shows created_at column. 
                                // Eloquent expects updated_at too by default.
                                // If table only has created_at, we should disable timestamps and handle created_at manually or configure const CREATED_AT.
                                // Let's assume standard Laravel timestamps but maybe only created_at exists? 
                                // User screenshot shows `created_at` with value. 
                                // Safest is to disable timestamps and if needed, I'll add `protected $dates = ['created_at'];`

    protected $fillable = [
        'register_id',
        'filename',
        'original_name',
        'mime_type',
        'size_bytes',
        'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function register()
    {
        return $this->belongsTo(Register::class);
    }
}
