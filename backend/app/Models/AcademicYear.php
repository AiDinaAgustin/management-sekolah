<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    protected $table = 'tahun_ajarans';

    protected $fillable = ['nama_tahun_ajaran', 'semester', 'status_aktif'];

    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class, 'tahun_ajaran_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'tahun_ajaran_id');
    }
}
