<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Anggaran extends Model
{
    use SoftDeletes;

    protected $table = 'anggaran';

    protected $fillable = [
        'periode',
        'kategori',
        'jumlah',
        'ambang',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'ambang' => 'integer',
        ];
    }
}
