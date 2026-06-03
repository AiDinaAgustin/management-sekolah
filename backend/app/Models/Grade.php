<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grade extends Model
{
    protected $table = 'nilais';

    protected $fillable = [
        'siswa_id',
        'mapel_id',
        'guru_id',
        'semester',
        'tugas',
        'uts',
        'uas',
        'nilai_akhir',
    ];

    protected $casts = [
        'tugas' => 'float',
        'uts' => 'float',
        'uas' => 'float',
        'nilai_akhir' => 'float',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'siswa_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'mapel_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'guru_id');
    }
}
