<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Embeddings extends Model
{
    use HasFactory;

    protected $fillable = [
        'image_id',
        'embedding',
        'cluster',
        'image_path'
    ];

    public function image()
    {
        return $this->belongsTo(Image::class);
    }
}
