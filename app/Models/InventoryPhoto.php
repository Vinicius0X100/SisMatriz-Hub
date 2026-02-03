<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryPhoto extends Model
{
    use HasFactory;

    protected $table = 'inventario_fotos';

    protected $fillable = [
        'i_id',
        'filename',
    ];
    
    // Table has created_at but not updated_at based on screenshot
    const UPDATED_AT = null;

    public function item()
    {
        return $this->belongsTo(Inventory::class, 'i_id', 'i_id');
    }
}
