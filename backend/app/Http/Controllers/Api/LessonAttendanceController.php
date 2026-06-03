<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LessonAttendance;
use App\Models\LessonMeeting;
use App\Models\Schedule;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LessonAttendanceController extends Controller
{
    public function schedules(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'teacher_id' => ['nullable', 'exists:teachers,id'],
        ]);

        $user = $request->user();
        $teacherId = $this->resolveScheduleTeacherId($request, $validated['teacher_id'] ?? null);
        $dayName = $this->mapDayName($validated['tanggal']);

        $schedules = Schedule::query()
            ->with(['classroom', 'teacher', 'subject', 'academicYear'])
            ->where('hari', $dayName)
            ->when($teacherId, function ($query) use ($teacherId) {
                $query->where('teacher_id', $teacherId);
            })
            ->orderBy('jam_mulai')
            ->get();

        $meetings = LessonMeeting::query()
            ->whereIn('schedule_id', $schedules->pluck('id'))
            ->whereDate('tanggal', $validated['tanggal'])
            ->withCount('lessonAttendances')
            ->get()
            ->keyBy('schedule_id');

        $data = $schedules->map(function (Schedule $schedule) use ($meetings) {
            $meeting = $meetings->get($schedule->id);

            return [
                'schedule_id' => $schedule->id,
                'hari' => $schedule->hari,
                'jam_mulai' => $schedule->jam_mulai,
                'jam_selesai' => $schedule->jam_selesai,
                'ruangan' => $schedule->ruangan,
                'kelas' => $schedule->classroom ? [
                    'id' => $schedule->classroom->id,
                    'nama_kelas' => $schedule->classroom->nama_kelas,
                ] : null,
                'teacher' => $schedule->teacher ? [
                    'id' => $schedule->teacher->id,
                    'nama' => $schedule->teacher->nama,
                    'nip' => $schedule->teacher->nip,
                ] : null,
                'subject' => $schedule->subject ? [
                    'id' => $schedule->subject->id,
                    'kode_mapel' => $schedule->subject->kode_mapel,
                    'nama_mapel' => $schedule->subject->nama_mapel,
                ] : null,
                'academic_year' => $schedule->academicYear ? [
                    'id' => $schedule->academicYear->id,
                    'nama_tahun_ajaran' => $schedule->academicYear->nama_tahun_ajaran,
                    'semester' => $schedule->academicYear->semester,
                ] : null,
                'meeting' => $meeting ? [
                    'id' => $meeting->id,
                    'pertemuan_ke' => $meeting->pertemuan_ke,
                    'topik' => $meeting->topik,
                    'catatan' => $meeting->catatan,
                    'status' => $meeting->status,
                    'attendance_count' => $meeting->lesson_attendances_count,
                ] : null,
            ];
        })->values();

        return response()->json(['data' => $data]);
    }

    public function open(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'schedule_id' => ['required', 'exists:jadwal_pelajarans,id'],
            'tanggal' => ['required', 'date'],
        ]);

        $schedule = Schedule::query()
            ->with(['classroom', 'teacher', 'subject', 'academicYear'])
            ->findOrFail($validated['schedule_id']);

        $this->authorizeScheduleAccess($request, $schedule);

        $meeting = LessonMeeting::query()->firstOrCreate(
            [
                'schedule_id' => $schedule->id,
                'tanggal' => $validated['tanggal'],
            ],
            [
                'pertemuan_ke' => $this->nextMeetingNumber($schedule->id),
                'status' => 'draft',
            ]
        );

        return response()->json([
            'data' => $this->buildMeetingPayload($meeting->fresh(), $schedule),
        ]);
    }

    public function show(Request $request, LessonMeeting $lessonMeeting): JsonResponse
    {
        $lessonMeeting->load(['schedule.classroom', 'schedule.teacher', 'schedule.subject', 'schedule.academicYear']);
        $this->authorizeScheduleAccess($request, $lessonMeeting->schedule);

        return response()->json([
            'data' => $this->buildMeetingPayload($lessonMeeting, $lessonMeeting->schedule),
        ]);
    }

    public function sheet(Request $request, LessonMeeting $lessonMeeting): JsonResponse
    {
        $lessonMeeting->load(['schedule.classroom', 'schedule.teacher', 'schedule.subject']);
        $this->authorizeScheduleAccess($request, $lessonMeeting->schedule);

        $students = Student::query()
            ->with('classroom')
            ->where('kelas_id', $lessonMeeting->schedule->kelas_id)
            ->where('status_aktif', true)
            ->orderBy('nama_lengkap')
            ->get();

        $existingAttendances = LessonAttendance::query()
            ->where('lesson_meeting_id', $lessonMeeting->id)
            ->get()
            ->keyBy('siswa_id');

        return response()->json([
            'data' => [
                'meeting' => $this->buildMeetingPayload($lessonMeeting, $lessonMeeting->schedule),
                'students' => $students->map(function (Student $student) use ($existingAttendances) {
                    $attendance = $existingAttendances->get($student->id);

                    return [
                        'id' => $student->id,
                        'nis' => $student->nis,
                        'nama_lengkap' => $student->nama_lengkap,
                        'jenis_kelamin' => $student->jenis_kelamin,
                        'kelas' => $student->classroom ? [
                            'id' => $student->classroom->id,
                            'nama_kelas' => $student->classroom->nama_kelas,
                        ] : null,
                        'attendance' => $attendance ? [
                            'id' => $attendance->id,
                            'status' => $attendance->status,
                            'keterangan' => $attendance->keterangan,
                        ] : null,
                    ];
                })->values(),
            ],
        ]);
    }

    public function save(Request $request, LessonMeeting $lessonMeeting): JsonResponse
    {
        $validated = $request->validate([
            'topik' => ['nullable', 'string', 'max:255'],
            'catatan' => ['nullable', 'string'],
            'status' => ['nullable', 'in:draft,selesai'],
            'attendances' => ['required', 'array', 'min:1'],
            'attendances.*.siswa_id' => ['required', 'exists:students,id'],
            'attendances.*.status' => ['required', 'in:hadir,izin,sakit,alfa'],
            'attendances.*.keterangan' => ['nullable', 'string'],
        ]);

        $lessonMeeting->load('schedule');
        $this->authorizeScheduleAccess($request, $lessonMeeting->schedule);

        $studentIds = collect($validated['attendances'])->pluck('siswa_id')->all();
        $validStudentCount = Student::query()
            ->whereIn('id', $studentIds)
            ->where('kelas_id', $lessonMeeting->schedule->kelas_id)
            ->count();

        if ($validStudentCount !== count($studentIds)) {
            throw ValidationException::withMessages([
                'attendances' => 'Ada siswa yang tidak sesuai dengan kelas pada jadwal ini.',
            ]);
        }

        DB::transaction(function () use ($lessonMeeting, $validated) {
            $lessonMeeting->update([
                'topik' => $validated['topik'] ?? $lessonMeeting->topik,
                'catatan' => $validated['catatan'] ?? $lessonMeeting->catatan,
                'status' => $validated['status'] ?? 'selesai',
            ]);

            foreach ($validated['attendances'] as $attendance) {
                LessonAttendance::query()->updateOrCreate(
                    [
                        'lesson_meeting_id' => $lessonMeeting->id,
                        'siswa_id' => $attendance['siswa_id'],
                    ],
                    [
                        'status' => $attendance['status'],
                        'keterangan' => $attendance['keterangan'] ?? null,
                    ]
                );
            }
        });

        return response()->json([
            'message' => 'Absensi per pertemuan berhasil disimpan.',
        ]);
    }

    protected function resolveScheduleTeacherId(Request $request, ?int $teacherId): ?int
    {
        $user = $request->user()->loadMissing('teacher');

        if ($user->role === 'guru') {
            return $user->teacher ? $user->teacher->id : 0;
        }

        return $teacherId;
    }

    protected function authorizeScheduleAccess(Request $request, Schedule $schedule): void
    {
        $user = $request->user()->loadMissing('teacher');

        if ($user->role !== 'guru') {
            return;
        }

        if (! $user->teacher || (int) $user->teacher->id !== (int) $schedule->teacher_id) {
            throw ValidationException::withMessages([
                'schedule_id' => 'Anda tidak memiliki akses ke sesi pelajaran ini.',
            ]);
        }
    }

    protected function nextMeetingNumber(int $scheduleId): int
    {
        $lastMeetingNumber = LessonMeeting::query()
            ->where('schedule_id', $scheduleId)
            ->max('pertemuan_ke');

        return ((int) $lastMeetingNumber) + 1;
    }

    protected function mapDayName(string $date): string
    {
        $dayMap = [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu',
        ];

        $englishDay = Carbon::parse($date)->format('l');

        return $dayMap[$englishDay] ?? $englishDay;
    }

    protected function buildMeetingPayload(LessonMeeting $meeting, Schedule $schedule): array
    {
        return [
            'id' => $meeting->id,
            'tanggal' => optional($meeting->tanggal)->format('Y-m-d'),
            'pertemuan_ke' => $meeting->pertemuan_ke,
            'topik' => $meeting->topik,
            'catatan' => $meeting->catatan,
            'status' => $meeting->status,
            'schedule' => [
                'id' => $schedule->id,
                'hari' => $schedule->hari,
                'jam_mulai' => $schedule->jam_mulai,
                'jam_selesai' => $schedule->jam_selesai,
                'ruangan' => $schedule->ruangan,
                'kelas' => $schedule->classroom ? [
                    'id' => $schedule->classroom->id,
                    'nama_kelas' => $schedule->classroom->nama_kelas,
                ] : null,
                'teacher' => $schedule->teacher ? [
                    'id' => $schedule->teacher->id,
                    'nama' => $schedule->teacher->nama,
                    'nip' => $schedule->teacher->nip,
                ] : null,
                'subject' => $schedule->subject ? [
                    'id' => $schedule->subject->id,
                    'kode_mapel' => $schedule->subject->kode_mapel,
                    'nama_mapel' => $schedule->subject->nama_mapel,
                ] : null,
                'academic_year' => $schedule->academicYear ? [
                    'id' => $schedule->academicYear->id,
                    'nama_tahun_ajaran' => $schedule->academicYear->nama_tahun_ajaran,
                    'semester' => $schedule->academicYear->semester,
                ] : null,
            ],
        ];
    }
}
