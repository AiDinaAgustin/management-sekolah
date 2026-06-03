<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $fillable = [
        'nis',
        'nama_lengkap',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'kelas_id',
        'orang_tua_id',
        'status_aktif',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date:Y-m-d',
        'status_aktif' => 'boolean',
    ];

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'kelas_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ParentModel::class, 'orang_tua_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'siswa_id');
    }

    public function lessonAttendances(): HasMany
    {
        return $this->hasMany(LessonAttendance::class, 'siswa_id');
    }
}
