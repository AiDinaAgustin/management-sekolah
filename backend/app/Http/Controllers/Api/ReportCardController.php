<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\SchoolProfile;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ReportCardController extends Controller
{
    public function show(Request $request, $studentId): JsonResponse
    {
        $validated = $request->validate([
            'semester' => ['nullable', 'string', 'max:30'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date'],
        ]);

        $student = Student::query()
            ->with(['classroom.waliKelas', 'classroom.academicYear'])
            ->findOrFail($studentId);

        $semester = $validated['semester'] ?? null;
        $start = $validated['tanggal_mulai'] ?? null;
        $end = $validated['tanggal_selesai'] ?? null;

        $schoolProfile = SchoolProfile::query()->first();
        $classAverages = $this->classAverages($student->kelas_id, $semester);

        return response()->json([
            'data' => $this->buildReportCard($student, $semester, $start, $end, $schoolProfile, $classAverages),
        ]);
    }

    public function klass(Request $request, $classroomId): JsonResponse
    {
        $validated = $request->validate([
            'semester' => ['nullable', 'string', 'max:30'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date'],
        ]);

        $classroom = Classroom::query()->findOrFail($classroomId);
        $semester = $validated['semester'] ?? null;
        $start = $validated['tanggal_mulai'] ?? null;
        $end = $validated['tanggal_selesai'] ?? null;

        $students = Student::query()
            ->with(['classroom.waliKelas', 'classroom.academicYear'])
            ->where('kelas_id', $classroom->id)
            ->where('status_aktif', true)
            ->orderBy('nama_lengkap')
            ->get();

        $schoolProfile = SchoolProfile::query()->first();
        $classAverages = $this->classAverages($classroom->id, $semester);

        $reportCards = $students->map(function (Student $student) use ($semester, $start, $end, $schoolProfile, $classAverages) {
            return $this->buildReportCard($student, $semester, $start, $end, $schoolProfile, $classAverages);
        })->values();

        return response()->json([
            'data' => [
                'classroom' => [
                    'id' => $classroom->id,
                    'nama_kelas' => $classroom->nama_kelas,
                ],
                'semester' => $semester,
                'report_cards' => $reportCards,
            ],
        ]);
    }

    protected function buildReportCard(Student $student, ?string $semester, ?string $start, ?string $end, $schoolProfile, Collection $classAverages): array
    {
        $availableSemesters = Grade::query()
            ->where('siswa_id', $student->id)
            ->distinct()
            ->orderBy('semester')
            ->pluck('semester')
            ->values();

        $grades = Grade::query()
            ->with('subject')
            ->where('siswa_id', $student->id)
            ->when($semester, fn ($query) => $query->where('semester', $semester))
            ->get()
            ->sortBy(fn ($grade) => optional($grade->subject)->nama_mapel)
            ->values()
            ->map(function (Grade $grade) {
                return [
                    'id' => $grade->id,
                    'mapel_id' => $grade->mapel_id,
                    'kode_mapel' => optional($grade->subject)->kode_mapel,
                    'nama_mapel' => optional($grade->subject)->nama_mapel ?? '-',
                    'tugas' => (float) $grade->tugas,
                    'uts' => (float) $grade->uts,
                    'uas' => (float) $grade->uas,
                    'nilai_akhir' => (float) $grade->nilai_akhir,
                    'predikat' => $this->predikat((float) $grade->nilai_akhir),
                ];
            });

        $average = $grades->isNotEmpty()
            ? round($grades->avg('nilai_akhir'), 2)
            : null;

        $ranking = $this->resolveRanking($student, $classAverages);

        $attendanceQuery = Attendance::query()->where('siswa_id', $student->id);

        if ($start && $end) {
            $attendanceQuery->whereBetween('tanggal', [$start, $end]);
        }

        $attendanceCounts = $attendanceQuery
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $attendanceRecap = [
            'hadir' => (int) ($attendanceCounts['hadir'] ?? 0),
            'izin' => (int) ($attendanceCounts['izin'] ?? 0),
            'sakit' => (int) ($attendanceCounts['sakit'] ?? 0),
            'alfa' => (int) ($attendanceCounts['alfa'] ?? 0),
        ];
        $attendanceRecap['total'] = array_sum($attendanceRecap);

        return [
            'student' => [
                'id' => $student->id,
                'nis' => $student->nis,
                'nama_lengkap' => $student->nama_lengkap,
                'jenis_kelamin' => $student->jenis_kelamin,
                'kelas' => $student->classroom ? [
                    'id' => $student->classroom->id,
                    'nama_kelas' => $student->classroom->nama_kelas,
                    'wali_kelas' => optional($student->classroom->waliKelas)->nama,
                    'tahun_ajaran' => $student->classroom->academicYear
                        ? $student->classroom->academicYear->nama_tahun_ajaran.' - '.$student->classroom->academicYear->semester
                        : null,
                ] : null,
            ],
            'semester' => $semester,
            'available_semesters' => $availableSemesters,
            'grades' => $grades,
            'average' => $average,
            'average_predikat' => $average !== null ? $this->predikat($average) : null,
            'ranking' => $ranking,
            'attendance_recap' => $attendanceRecap,
            'school' => [
                'nama_sekolah' => $schoolProfile->nama_sekolah ?? 'Sekolah',
                'tagline' => $schoolProfile->tagline ?? null,
            ],
        ];
    }

    protected function classAverages(?int $kelasId, ?string $semester): Collection
    {
        if (! $kelasId) {
            return collect();
        }

        $classStudentIds = Student::query()
            ->where('kelas_id', $kelasId)
            ->pluck('id');

        return Grade::query()
            ->whereIn('siswa_id', $classStudentIds)
            ->when($semester, fn ($query) => $query->where('semester', $semester))
            ->selectRaw('siswa_id, AVG(nilai_akhir) as avg_nilai')
            ->groupBy('siswa_id')
            ->pluck('avg_nilai', 'siswa_id')
            ->map(fn ($value) => (float) $value);
    }

    protected function resolveRanking(Student $student, Collection $classAverages): array
    {
        if ($classAverages->isEmpty() || ! $classAverages->has($student->id)) {
            return ['position' => null, 'total' => $classAverages->count()];
        }

        $sorted = $classAverages
            ->sortDesc()
            ->keys()
            ->values();

        $index = $sorted->search($student->id);

        return [
            'position' => $index === false ? null : $index + 1,
            'total' => $classAverages->count(),
        ];
    }

    protected function predikat(float $nilai): string
    {
        if ($nilai >= 90) {
            return 'A';
        }

        if ($nilai >= 80) {
            return 'B';
        }

        if ($nilai >= 70) {
            return 'C';
        }

        return 'D';
    }

    public function pdf($studentId): JsonResponse
    {
        // Pembuatan PDF ditangani di sisi frontend lewat fitur cetak browser (window.print()).
        return response()->json([
            'message' => 'Gunakan fitur cetak pada halaman rapot untuk mengunduh PDF.',
            'student_id' => $studentId,
        ]);
    }
}
