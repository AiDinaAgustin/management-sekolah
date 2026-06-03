<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GradeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kelas_id' => ['required', 'exists:kelas,id'],
            'mapel_id' => ['required', 'exists:mata_pelajarans,id'],
            'semester' => ['required', 'string', 'max:30'],
            'teacher_id' => ['nullable', 'exists:teachers,id'],
        ]);

        $teacherId = $this->resolveTeacherId($request, $validated['teacher_id'] ?? null);
        $this->ensureTeacherOwnsClassSubject($request, (int) $validated['kelas_id'], (int) $validated['mapel_id'], $teacherId);

        $students = Student::query()
            ->where('kelas_id', $validated['kelas_id'])
            ->where('status_aktif', true)
            ->orderBy('nama_lengkap')
            ->get();

        $grades = Grade::query()
            ->where('mapel_id', $validated['mapel_id'])
            ->where('semester', $validated['semester'])
            ->whereIn('siswa_id', $students->pluck('id'))
            ->get()
            ->keyBy('siswa_id');

        return response()->json([
            'data' => $students->map(function (Student $student) use ($grades) {
                $grade = $grades->get($student->id);

                return [
                    'siswa_id' => $student->id,
                    'nis' => $student->nis,
                    'nama_lengkap' => $student->nama_lengkap,
                    'jenis_kelamin' => $student->jenis_kelamin,
                    'grade' => $grade ? [
                        'id' => $grade->id,
                        'tugas' => $grade->tugas,
                        'uts' => $grade->uts,
                        'uas' => $grade->uas,
                        'nilai_akhir' => $grade->nilai_akhir,
                    ] : null,
                ];
            })->values(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kelas_id' => ['required', 'exists:kelas,id'],
            'mapel_id' => ['required', 'exists:mata_pelajarans,id'],
            'semester' => ['required', 'string', 'max:30'],
            'teacher_id' => ['nullable', 'exists:teachers,id'],
            'grades' => ['required', 'array', 'min:1'],
            'grades.*.siswa_id' => ['required', 'exists:students,id'],
            'grades.*.tugas' => ['required', 'numeric', 'min:0', 'max:100'],
            'grades.*.uts' => ['required', 'numeric', 'min:0', 'max:100'],
            'grades.*.uas' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $teacherId = $this->resolveTeacherId($request, $validated['teacher_id'] ?? null);
        $this->ensureTeacherOwnsClassSubject($request, (int) $validated['kelas_id'], (int) $validated['mapel_id'], $teacherId);
        $this->ensureStudentsMatchClass((int) $validated['kelas_id'], $validated['grades']);

        DB::transaction(function () use ($validated, $teacherId) {
            foreach ($validated['grades'] as $row) {
                $nilaiAkhir = round((((float) $row['tugas']) + ((float) $row['uts']) + ((float) $row['uas'])) / 3, 2);

                Grade::query()->updateOrCreate(
                    [
                        'siswa_id' => $row['siswa_id'],
                        'mapel_id' => $validated['mapel_id'],
                        'semester' => $validated['semester'],
                    ],
                    [
                        'guru_id' => $teacherId,
                        'tugas' => $row['tugas'],
                        'uts' => $row['uts'],
                        'uas' => $row['uas'],
                        'nilai_akhir' => $nilaiAkhir,
                    ]
                );
            }
        });

        return response()->json([
            'message' => 'Nilai berhasil disimpan.',
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $grade = Grade::query()
            ->with(['student.classroom', 'subject', 'teacher'])
            ->findOrFail($id);

        return response()->json(['data' => $grade]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $grade = Grade::query()->findOrFail($id);

        $validated = $request->validate([
            'tugas' => ['required', 'numeric', 'min:0', 'max:100'],
            'uts' => ['required', 'numeric', 'min:0', 'max:100'],
            'uas' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $teacherId = $this->resolveTeacherId($request, null);

        if ($request->user()->role === 'guru' && (int) $grade->guru_id !== (int) $teacherId) {
            throw ValidationException::withMessages([
                'grade' => 'Anda tidak memiliki akses untuk mengubah nilai ini.',
            ]);
        }

        $grade->update([
            'tugas' => $validated['tugas'],
            'uts' => $validated['uts'],
            'uas' => $validated['uas'],
            'nilai_akhir' => round((((float) $validated['tugas']) + ((float) $validated['uts']) + ((float) $validated['uas'])) / 3, 2),
        ]);

        return response()->json([
            'message' => 'Nilai berhasil diperbarui.',
            'data' => $grade->fresh(),
        ]);
    }

    public function recap(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kelas_id' => ['nullable', 'exists:kelas,id'],
            'mapel_id' => ['nullable', 'exists:mata_pelajarans,id'],
            'semester' => ['nullable', 'string', 'max:30'],
        ]);

        $query = Grade::query()->with(['student.classroom', 'subject', 'teacher']);

        if (! empty($validated['kelas_id'])) {
            $query->whereHas('student', function ($studentQuery) use ($validated) {
                $studentQuery->where('kelas_id', $validated['kelas_id']);
            });
        }

        if (! empty($validated['mapel_id'])) {
            $query->where('mapel_id', $validated['mapel_id']);
        }

        if (! empty($validated['semester'])) {
            $query->where('semester', $validated['semester']);
        }

        if ($request->user()->role === 'guru') {
            $teacherId = $this->resolveTeacherId($request, null);
            $query->where('guru_id', $teacherId);
        }

        return response()->json([
            'data' => $query->orderByDesc('updated_at')->get(),
        ]);
    }

    public function options(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'teacher_id' => ['nullable', 'exists:teachers,id'],
        ]);

        $teacherId = $this->resolveTeacherId($request, $validated['teacher_id'] ?? null);

        $schedulePairs = Schedule::query()
            ->with(['classroom', 'subject'])
            ->when($teacherId, function ($query) use ($teacherId) {
                $query->where('teacher_id', $teacherId);
            })
            ->get()
            ->groupBy('kelas_id')
            ->map(function ($items) {
                $first = $items->first();

                return [
                    'kelas' => $first && $first->classroom ? [
                        'id' => $first->classroom->id,
                        'nama_kelas' => $first->classroom->nama_kelas,
                    ] : null,
                    'subjects' => $items
                        ->filter(function ($schedule) {
                            return (bool) $schedule->subject;
                        })
                        ->map(function ($schedule) {
                            return [
                                'id' => $schedule->subject->id,
                                'kode_mapel' => $schedule->subject->kode_mapel,
                                'nama_mapel' => $schedule->subject->nama_mapel,
                            ];
                        })
                        ->unique('id')
                        ->values(),
                ];
            })
            ->values();

        return response()->json([
            'data' => [
                'teacher_id' => $teacherId,
                'classrooms' => $schedulePairs,
                'semesters' => ['Ganjil', 'Genap'],
            ],
        ]);
    }

    protected function resolveTeacherId(Request $request, ?int $requestedTeacherId): int
    {
        $user = $request->user()->loadMissing('teacher');

        if ($user->role === 'guru') {
            if (! $user->teacher) {
                throw ValidationException::withMessages([
                    'teacher_id' => 'Akun guru belum terhubung dengan data teacher.',
                ]);
            }

            return (int) $user->teacher->id;
        }

        if ($requestedTeacherId) {
            $teacherExists = Teacher::query()->whereKey($requestedTeacherId)->exists();
            if (! $teacherExists) {
                throw ValidationException::withMessages([
                    'teacher_id' => 'Guru yang dipilih tidak valid.',
                ]);
            }

            return $requestedTeacherId;
        }

        throw ValidationException::withMessages([
            'teacher_id' => 'Guru wajib dipilih.',
        ]);
    }

    protected function ensureTeacherOwnsClassSubject(Request $request, int $classroomId, int $subjectId, int $teacherId): void
    {
        if ($request->user()->role !== 'guru') {
            return;
        }

        $hasSchedule = Schedule::query()
            ->where('teacher_id', $teacherId)
            ->where('kelas_id', $classroomId)
            ->where('subject_id', $subjectId)
            ->exists();

        if (! $hasSchedule) {
            throw ValidationException::withMessages([
                'kelas_id' => 'Anda tidak memiliki akses untuk input nilai pada kelas dan mapel ini.',
            ]);
        }
    }

    protected function ensureStudentsMatchClass(int $classroomId, array $grades): void
    {
        $studentIds = collect($grades)->pluck('siswa_id')->all();

        $validCount = Student::query()
            ->whereIn('id', $studentIds)
            ->where('kelas_id', $classroomId)
            ->count();

        if ($validCount !== count($studentIds)) {
            throw ValidationException::withMessages([
                'grades' => 'Ada siswa yang tidak sesuai dengan kelas yang dipilih.',
            ]);
        }
    }
}
