<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventory';
    protected $primaryKey = 'i_id';

    const CREATED_AT = null;
    const UPDATED_AT = 'last_update';

    protected $fillable = [
        'item',
        'category',
        'ent_id',
        'sala_id',
        'description',
        'qntd_destributed',
        'paroquia_id',
    ];

    public function categoria()
    {
        return $this->belongsTo(CategoriaDoacao::class, 'category'); // Assumes CategoriaDoacao has 'id' as PK? Wait, let me check CategoriaDoacao PK.
    }

    public function comunidade()
    {
        return $this->belongsTo(Entidade::class, 'ent_id', 'ent_id');
    }

    public function local()
    {
        return $this->belongsTo(ReservaLocal::class, 'sala_id');
    }

    public function photos()
    {
        return $this->hasMany(InventoryPhoto::class, 'i_id', 'i_id');
    }
}
