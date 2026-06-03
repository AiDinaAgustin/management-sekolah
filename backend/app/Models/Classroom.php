<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\AcademicYear;

class Classroom extends Model
{
    protected $table = 'kelas';

    protected $fillable = [
        'tingkat',
        'rombel',
        'nama_kelas',
        'wali_kelas_id',
        'tahun_ajaran_id',
    ];

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'kelas_id');
    }

    public function waliKelas(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'wali_kelas_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'tahun_ajaran_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'kelas_id');
    }
}
