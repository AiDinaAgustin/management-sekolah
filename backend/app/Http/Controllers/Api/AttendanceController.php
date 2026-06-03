<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\BulkStoreAttendanceRequest;
use App\Http\Requests\Attendance\IndexAttendanceRequest;
use App\Http\Requests\Attendance\RecapAttendanceRequest;
use App\Http\Resources\AttendanceResource;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceController extends Controller
{
    public function index(IndexAttendanceRequest $request)
    {
        $user = $request->user();
        $validated = $request->validated();
        $perPage = (int) ($validated['per_page'] ?? 10);
        $perPage = $perPage > 0 ? min($perPage, 100) : 10;

        $query = Attendance::query()
            ->with(['student.classroom', 'teacher'])
            ->when($user && $user->role === 'guru', function ($builder) use ($user) {
                $teacherId = optional($user->loadMissing('teacher')->teacher)->id;

                if (! $teacherId) {
                    $builder->whereRaw('1 = 0');
                    return;
                }

                $builder->where('guru_id', $teacherId);
            })
            ->when(! empty($validated['tanggal']), function ($builder) use ($validated) {
                $builder->whereDate('tanggal', $validated['tanggal']);
            })
            ->when(! empty($validated['kelas_id']), function ($builder) use ($validated) {
                $builder->whereHas('student', function ($studentQuery) use ($validated) {
                    $studentQuery->where('kelas_id', $validated['kelas_id']);
                });
            })
            ->when(! empty($validated['guru_id']), function ($builder) use ($validated) {
                $builder->where('guru_id', $validated['guru_id']);
            })
            ->when(! empty($validated['status']), function ($builder) use ($validated) {
                $builder->where('status', $validated['status']);
            })
            ->when(! empty($validated['search']), function ($builder) use ($validated) {
                $search = $validated['search'];
                $builder->whereHas('student', function ($studentQuery) use ($search) {
                    $studentQuery
                        ->where('nama_lengkap', 'ilike', '%'.$search.'%')
                        ->orWhere('nis', 'ilike', '%'.$search.'%');
                });
            })
            ->orderByDesc('tanggal')
            ->orderBy('siswa_id')
            ->paginate($perPage)
            ->appends($request->query());

        return AttendanceResource::collection($query);
    }

    public function bulkStore(BulkStoreAttendanceRequest $request)
    {
        $validated = $request->validated();
        $actorTeacherId = $this->resolveTeacherId($request, $validated['guru_id'] ?? null);
        $studentIds = collect($validated['attendances'])->pluck('siswa_id')->all();

        $validStudentCount = Student::query()
            ->whereIn('id', $studentIds)
            ->where('kelas_id', $validated['kelas_id'])
            ->count();

        if ($validStudentCount !== count($studentIds)) {
            throw ValidationException::withMessages([
                'kelas_id' => 'Ada siswa yang tidak sesuai dengan kelas yang dipilih.',
            ]);
        }

        DB::transaction(function () use ($validated, $actorTeacherId) {
            foreach ($validated['attendances'] as $attendanceInput) {
                Attendance::updateOrCreate(
                    [
                        'siswa_id' => $attendanceInput['siswa_id'],
                        'tanggal' => $validated['tanggal'],
                    ],
                    [
                        'status' => $attendanceInput['status'],
                        'keterangan' => $attendanceInput['keterangan'] ?? null,
                        'guru_id' => $actorTeacherId,
                    ],
                );
            }
        });

        $records = Attendance::query()
            ->with(['student.classroom', 'teacher'])
            ->whereDate('tanggal', $validated['tanggal'])
            ->whereIn('siswa_id', $studentIds)
            ->get();

        return AttendanceResource::collection($records)
            ->additional(['message' => 'Absensi harian berhasil disimpan']);
    }

    public function recap(RecapAttendanceRequest $request)
    {
        $user = $request->user();
        $validated = $request->validated();
        $startDate = $validated['tanggal_mulai'] ?? null;
        $endDate = $validated['tanggal_selesai'] ?? null;

        $baseQuery = Attendance::query()
            ->with(['student.classroom'])
            ->when($user && $user->role === 'guru', function ($builder) use ($user) {
                $teacherId = optional($user->loadMissing('teacher')->teacher)->id;

                if (! $teacherId) {
                    $builder->whereRaw('1 = 0');
                    return;
                }

                $builder->where('guru_id', $teacherId);
            })
            ->when(! empty($validated['kelas_id']), function ($builder) use ($validated) {
                $builder->whereHas('student', function ($studentQuery) use ($validated) {
                    $studentQuery->where('kelas_id', $validated['kelas_id']);
                });
            })
            ->when($startDate && $endDate, function ($builder) use ($startDate, $endDate) {
                $builder->whereBetween('tanggal', [$startDate, $endDate]);
            })
            ->when(! $startDate && ! $endDate && ! empty($validated['bulan']) && ! empty($validated['tahun']), function ($builder) use ($validated) {
                $builder->whereMonth('tanggal', $validated['bulan'])->whereYear('tanggal', $validated['tahun']);
            });

        $summary = (clone $baseQuery)
            ->selectRaw("status, count(*) as total")
            ->groupBy('status')
            ->pluck('total', 'status');

        $perStudent = (clone $baseQuery)
            ->selectRaw("
                siswa_id,
                SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) as hadir,
                SUM(CASE WHEN status = 'izin' THEN 1 ELSE 0 END) as izin,
                SUM(CASE WHEN status = 'sakit' THEN 1 ELSE 0 END) as sakit,
                SUM(CASE WHEN status = 'alfa' THEN 1 ELSE 0 END) as alfa
            ")
            ->groupBy('siswa_id')
            ->with(['student.classroom'])
            ->get()
            ->map(function ($item) {
                return [
                    'siswa_id' => $item->siswa_id,
                    'student' => $item->student ? [
                        'id' => $item->student->id,
                        'nis' => $item->student->nis,
                        'nama_lengkap' => $item->student->nama_lengkap,
                        'kelas' => $item->student->classroom ? [
                            'id' => $item->student->classroom->id,
                            'nama_kelas' => $item->student->classroom->nama_kelas,
                        ] : null,
                    ] : null,
                    'hadir' => (int) $item->hadir,
                    'izin' => (int) $item->izin,
                    'sakit' => (int) $item->sakit,
                    'alfa' => (int) $item->alfa,
                ];
            });

        return response()->json([
            'summary' => [
                'hadir' => (int) ($summary['hadir'] ?? 0),
                'izin' => (int) ($summary['izin'] ?? 0),
                'sakit' => (int) ($summary['sakit'] ?? 0),
                'alfa' => (int) ($summary['alfa'] ?? 0),
            ],
            'students' => $perStudent,
        ]);
    }

    protected function resolveTeacherId(Request $request, ?int $requestedTeacherId = null): int
    {
        $user = $request->user()->loadMissing('teacher');

        if ($user->role === 'guru') {
            if (! $user->teacher) {
                throw ValidationException::withMessages([
                    'guru_id' => 'Akun guru belum terhubung dengan data teacher.',
                ]);
            }

            return $user->teacher->id;
        }

        if ($requestedTeacherId) {
            $teacherExists = Teacher::query()->whereKey($requestedTeacherId)->exists();

            if (! $teacherExists) {
                throw ValidationException::withMessages([
                    'guru_id' => 'Guru yang dipilih tidak valid.',
                ]);
            }

            return $requestedTeacherId;
        }

        throw ValidationException::withMessages([
            'guru_id' => 'Guru penginput wajib dipilih oleh admin.',
        ]);
    }

}
