<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Grade;
use App\Models\SchoolProfile;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        // Daftar semester yang sudah punya nilai untuk siswa ini.
        $availableSemesters = Grade::query()
            ->where('siswa_id', $student->id)
            ->distinct()
            ->orderBy('semester')
            ->pluck('semester')
            ->values();

        // Nilai per mata pelajaran.
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

        // Peringkat di dalam kelas berdasarkan rata-rata nilai akhir (semester yang sama).
        $ranking = $this->resolveRanking($student, $semester);

        // Rekap kehadiran.
        $attendanceQuery = Attendance::query()->where('siswa_id', $student->id);

        if (! empty($validated['tanggal_mulai']) && ! empty($validated['tanggal_selesai'])) {
            $attendanceQuery->whereBetween('tanggal', [$validated['tanggal_mulai'], $validated['tanggal_selesai']]);
        }

        $attendanceCounts = (clone $attendanceQuery)
            ->selectRaw("status, count(*) as total")
            ->groupBy('status')
            ->pluck('total', 'status');

        $attendanceRecap = [
            'hadir' => (int) ($attendanceCounts['hadir'] ?? 0),
            'izin' => (int) ($attendanceCounts['izin'] ?? 0),
            'sakit' => (int) ($attendanceCounts['sakit'] ?? 0),
            'alfa' => (int) ($attendanceCounts['alfa'] ?? 0),
        ];
        $attendanceRecap['total'] = array_sum($attendanceRecap);

        $schoolProfile = SchoolProfile::query()->first();

        return response()->json([
            'data' => [
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
            ],
        ]);
    }

    protected function resolveRanking(Student $student, ?string $semester): array
    {
        if (! $student->kelas_id) {
            return ['position' => null, 'total' => 0];
        }

        $classStudentIds = Student::query()
            ->where('kelas_id', $student->kelas_id)
            ->pluck('id');

        $averages = Grade::query()
            ->whereIn('siswa_id', $classStudentIds)
            ->when($semester, fn ($query) => $query->where('semester', $semester))
            ->selectRaw('siswa_id, AVG(nilai_akhir) as avg_nilai')
            ->groupBy('siswa_id')
            ->pluck('avg_nilai', 'siswa_id');

        if ($averages->isEmpty() || ! $averages->has($student->id)) {
            return ['position' => null, 'total' => $averages->count()];
        }

        $sorted = $averages
            ->map(fn ($value) => (float) $value)
            ->sortDesc()
            ->keys()
            ->values();

        $index = $sorted->search($student->id);

        return [
            'position' => $index === false ? null : $index + 1,
            'total' => $averages->count(),
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
