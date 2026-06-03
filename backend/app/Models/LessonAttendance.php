<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonAttendance extends Model
{
    protected $table = 'absensi_pelajarans';

    protected $fillable = [
        'lesson_meeting_id',
        'siswa_id',
        'status',
        'keterangan',
    ];

    public function lessonMeeting(): BelongsTo
    {
        return $this->belongsTo(LessonMeeting::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'siswa_id');
    }
}
