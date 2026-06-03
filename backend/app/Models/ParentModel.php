<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParentModel extends Model
{
    protected $table = 'orang_tuas';

    protected $fillable = [
        'nama_ayah',
        'no_hp_ayah',
        'pekerjaan_ayah',
        'nama_ibu',
        'no_hp_ibu',
        'pekerjaan_ibu',
        'alamat',
    ];

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'orang_tua_id');
    }
}
