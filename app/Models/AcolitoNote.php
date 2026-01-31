<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcolitoNote extends Model
{
    use HasFactory;

    protected $table = 'acolitos_notes';
    public $timestamps = false;

    protected $fillable = [
        'ac_id',
        'note',
        'send_at',
        'send_by',
        'paroquia_id',
    ];

    // Removed cast to avoid exception on invalid format
    // protected $casts = [
    //    'send_at' => 'datetime',
    // ];

    public function getSendAtAttribute($value)
    {
        if (!$value) return null;
        
        try {
            return \Carbon\Carbon::parse($value);
        } catch (\Exception $e) {
            try {
                return \Carbon\Carbon::createFromFormat('d/m/Y H:i', $value);
            } catch (\Exception $e2) {
                return $value;
            }
        }
    }

    public function acolito()
    {
        return $this->belongsTo(Acolito::class, 'ac_id');
    }

    public function register()
    {
        return $this->belongsTo(Register::class, 'send_by');
    }
}
