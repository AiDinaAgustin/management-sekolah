<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LessonMeeting extends Model
{
    protected $table = 'pertemuan_pelajarans';

    protected $fillable = [
        'schedule_id',
        'tanggal',
        'pertemuan_ke',
        'topik',
        'catatan',
        'status',
    ];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function lessonAttendances(): HasMany
    {
        return $this->hasMany(LessonAttendance::class, 'lesson_meeting_id');
    }
}
