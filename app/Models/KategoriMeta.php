<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KategoriMeta extends Model
{
    use SoftDeletes;

    protected $table = 'kategori_meta';

    protected $fillable = [
        'kategori',
        'ikon',
        'warna',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
        ];
    }
}
