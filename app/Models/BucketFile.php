<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BucketFile extends Model
{
    use HasFactory;

    protected $table = 'bucket_files';

    public $timestamps = false;

    protected $fillable = [
        'bucket_id',
        'bucket_rand',
        'file_name',
        'file_path',
        'file_size',
        'upload_date',
    ];

    protected $casts = [
        'file_size' => 'int',
    ];

    public function bucket()
    {
        return $this->belongsTo(Bucket::class, 'bucket_id');
    }
}

