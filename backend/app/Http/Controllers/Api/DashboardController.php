<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function admin()
    {
        $today = Carbon::today()->toDateString();
        $monthStart = Carbon::today()->startOfMonth()->toDateString();

        $totalStudents = Student::query()->where('status_aktif', true)->count();
        $totalTeachers = Teacher::query()->count();
        $totalClassrooms = Classroom::query()->count();
        $activeAcademicYear = AcademicYear::query()->where('status_aktif', true)->latest('id')->first();

        $attendanceToday = Attendance::query()->whereDate('tanggal', $today)->count();
        $attendancePresentToday = Attendance::query()
            ->whereDate('tanggal', $today)
            ->where('status', 'hadir')
            ->count();
        $attendanceRate = $attendanceToday > 0
            ? round(($attendancePresentToday / $attendanceToday) * 100, 1)
            : 0;

        $recentAttendances = Attendance::query()
            ->with(['student.classroom', 'teacher'])
            ->whereDate('tanggal', $today)
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->map(function ($attendance) {
                return [
                    'title' => 'Absensi '.$attendance->student->nama_lengkap,
                    'description' => ($attendance->student->classroom->nama_kelas ?? '-').' · '.$attendance->status,
                    'time' => optional($attendance->updated_at)->format('H:i'),
                    'tone' => $attendance->status === 'hadir' ? 'success' : 'warning',
                ];
            })
            ->values();

        $recentGrades = Grade::query()
            ->with(['student.classroom', 'subject'])
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->map(function ($grade) {
                return [
                    'title' => 'Nilai '.$grade->subject->nama_mapel,
                    'description' => $grade->student->nama_lengkap.' · '.($grade->student->classroom->nama_kelas ?? '-'),
                    'time' => optional($grade->updated_at)->format('d M H:i'),
                    'tone' => 'info',
                ];
            })
            ->values();

        return response()->json([
            'role' => 'admin',
            'summary' => [
                'total_siswa' => $totalStudents,
                'total_guru' => $totalTeachers,
                'total_kelas' => $totalClassrooms,
                'kehadiran_hari_ini' => $attendanceRate,
                'tahun_ajaran_aktif' => $activeAcademicYear ? $activeAcademicYear->nama_tahun_ajaran.' '.$activeAcademicYear->semester : '-',
                'input_nilai_bulan_ini' => Grade::query()->whereDate('updated_at', '>=', $monthStart)->count(),
            ],
            'agenda' => [
                [
                    'title' => 'Absensi Harian',
                    'subtitle' => 'Pantau dan lengkapi absensi siswa hari ini',
                    'meta' => $attendanceToday.' data · '.$attendancePresentToday.' hadir',
                    'tone' => 'primary',
                ],
                [
                    'title' => 'Input Nilai',
                    'subtitle' => 'Periksa progres input nilai guru',
                    'meta' => Grade::query()->whereDate('updated_at', '>=', $monthStart)->count().' update bulan ini',
                    'tone' => 'neutral',
                ],
                [
                    'title' => 'Tahun Ajaran Aktif',
                    'subtitle' => $activeAcademicYear ? $activeAcademicYear->nama_tahun_ajaran : 'Belum ditentukan',
                    'meta' => $activeAcademicYear ? $activeAcademicYear->semester : '-',
                    'tone' => 'success',
                ],
            ],
            'activities' => $recentAttendances->concat($recentGrades)->take(6)->values(),
            'shortcuts' => [
                ['label' => 'Kelola Siswa', 'to' => '/siswa'],
                ['label' => 'Input Absensi', 'to' => '/absensi'],
                ['label' => 'Input Nilai', 'to' => '/nilai'],
                ['label' => 'Absensi Mapel', 'to' => '/absensi-pelajaran'],
            ],
        ]);
    }

    public function guru(Request $request)
    {
        $user = $request->user()->loadMissing('teacher');
        $teacher = $user->teacher;
        $today = Carbon::today();
        $dayMap = [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu',
        ];
        $todayName = $dayMap[$today->format('l')] ?? $today->format('l');

        if (! $teacher) {
            return response()->json([
                'role' => 'guru',
                'summary' => [
                    'jadwal_hari_ini' => 0,
                    'kelas_diampu' => 0,
                    'input_nilai_selesai' => 0,
                    'absensi_hari_ini' => 0,
                ],
                'agenda' => [],
                'activities' => [],
                'shortcuts' => [
                    ['label' => 'Absensi Harian', 'to' => '/absensi'],
                    ['label' => 'Absensi Mapel', 'to' => '/absensi-pelajaran'],
                    ['label' => 'Input Nilai', 'to' => '/nilai'],
                ],
            ]);
        }

        $todaySchedules = Schedule::query()
            ->with(['classroom', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->where('hari', $todayName)
            ->orderBy('jam_mulai')
            ->get();

        $classroomIds = Schedule::query()
            ->where('teacher_id', $teacher->id)
            ->pluck('kelas_id')
            ->unique()
            ->values();

        $todayGradeUpdates = Grade::query()
            ->where('guru_id', $teacher->id)
            ->whereDate('updated_at', $today->toDateString())
            ->count();

        $todayAttendanceInputs = Attendance::query()
            ->where('guru_id', $teacher->id)
            ->whereDate('tanggal', $today->toDateString())
            ->count();

        return response()->json([
            'role' => 'guru',
            'summary' => [
                'jadwal_hari_ini' => $todaySchedules->count(),
                'kelas_diampu' => $classroomIds->count(),
                'input_nilai_selesai' => $todayGradeUpdates,
                'absensi_hari_ini' => $todayAttendanceInputs,
            ],
            'agenda' => $todaySchedules->map(function ($schedule) {
                return [
                    'title' => $schedule->subject->nama_mapel ?? 'Mata Pelajaran',
                    'subtitle' => $schedule->classroom->nama_kelas ?? '-',
                    'meta' => substr((string) $schedule->jam_mulai, 0, 5).' - '.substr((string) $schedule->jam_selesai, 0, 5),
                    'tone' => 'primary',
                ];
            })->values(),
            'activities' => [
                [
                    'title' => 'Kelas Diampu',
                    'description' => $classroomIds->count().' kelas aktif di jadwal mengajar',
                    'time' => $todayName,
                    'tone' => 'info',
                ],
                [
                    'title' => 'Input Nilai Hari Ini',
                    'description' => $todayGradeUpdates.' perubahan nilai tersimpan',
                    'time' => $today->format('d M'),
                    'tone' => 'success',
                ],
                [
                    'title' => 'Absensi Hari Ini',
                    'description' => $todayAttendanceInputs.' data absensi telah diinput',
                    'time' => $today->format('d M'),
                    'tone' => 'warning',
                ],
            ],
            'shortcuts' => [
                ['label' => 'Absensi Harian', 'to' => '/absensi'],
                ['label' => 'Absensi Mapel', 'to' => '/absensi-pelajaran'],
                ['label' => 'Input Nilai', 'to' => '/nilai'],
            ],
        ]);
    }
}
