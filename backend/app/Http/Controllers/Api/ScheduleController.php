<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Schedule\StoreScheduleRequest;
use App\Http\Requests\Schedule\UpdateScheduleRequest;
use App\Http\Resources\ScheduleResource;
use App\Models\Schedule;
use App\Models\TeacherSubject;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $perPage = (int) $request->get('per_page', 10);
        $perPage = $perPage > 0 ? min($perPage, 100) : 10;
        $allowedSorts = [
            'hari' => 'jadwal_pelajarans.hari',
            'jam_mulai' => 'jadwal_pelajarans.jam_mulai',
            'kelas' => 'kelas.nama_kelas',
            'teacher' => 'teachers.nama',
            'subject' => 'mata_pelajarans.nama_mapel',
        ];
        $sortBy = $request->get('sort_by', 'hari');
        $sortDirection = strtolower((string) $request->get('sort_direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        if (! array_key_exists($sortBy, $allowedSorts)) {
            $sortBy = 'hari';
        }

        $schedules = Schedule::query()
            ->select('jadwal_pelajarans.*')
            ->with(['academicYear', 'classroom', 'teacher', 'subject'])
            ->leftJoin('kelas', 'kelas.id', '=', 'jadwal_pelajarans.kelas_id')
            ->leftJoin('teachers', 'teachers.id', '=', 'jadwal_pelajarans.teacher_id')
            ->leftJoin('mata_pelajarans', 'mata_pelajarans.id', '=', 'jadwal_pelajarans.subject_id')
            ->when($user && $user->role === 'guru', function ($builder) use ($user) {
                $teacherId = optional($user->loadMissing('teacher')->teacher)->id;

                if (! $teacherId) {
                    $builder->whereRaw('1 = 0');
                    return;
                }

                $builder->where('jadwal_pelajarans.teacher_id', $teacherId);
            })
            ->when($request->filled('search'), function ($builder) use ($request) {
                $search = $request->get('search');
                $builder->where(function ($query) use ($search) {
                    $query
                        ->where('kelas.nama_kelas', 'ilike', '%'.$search.'%')
                        ->orWhere('teachers.nama', 'ilike', '%'.$search.'%')
                        ->orWhere('mata_pelajarans.nama_mapel', 'ilike', '%'.$search.'%')
                        ->orWhere('mata_pelajarans.kode_mapel', 'ilike', '%'.$search.'%');
                });
            })
            ->when($request->filled('tahun_ajaran_id'), function ($builder) use ($request) {
                $builder->where('jadwal_pelajarans.tahun_ajaran_id', $request->get('tahun_ajaran_id'));
            })
            ->when($request->filled('kelas_id'), function ($builder) use ($request) {
                $builder->where('jadwal_pelajarans.kelas_id', $request->get('kelas_id'));
            })
            ->when($request->filled('teacher_id'), function ($builder) use ($request) {
                $builder->where('jadwal_pelajarans.teacher_id', $request->get('teacher_id'));
            })
            ->when($request->filled('hari'), function ($builder) use ($request) {
                $builder->where('jadwal_pelajarans.hari', $request->get('hari'));
            })
            ->orderByRaw($this->dayOrderSql())
            ->orderBy($allowedSorts[$sortBy], $sortDirection)
            ->orderBy('jadwal_pelajarans.jam_mulai')
            ->paginate($perPage)
            ->appends($request->query());

        return ScheduleResource::collection($schedules);
    }

    public function store(StoreScheduleRequest $request)
    {
        $validated = $request->validated();
        $this->validateTeacherSubjectPair($validated['teacher_id'], $validated['subject_id']);
        $this->validateConflicts($validated);

        $schedule = Schedule::create($validated);

        return (new ScheduleResource($schedule->load(['academicYear', 'classroom', 'teacher', 'subject'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Schedule $schedule)
    {
        $this->authorizeGuruScheduleAccess(request(), $schedule);

        return new ScheduleResource($schedule->load(['academicYear', 'classroom', 'teacher', 'subject']));
    }

    public function update(UpdateScheduleRequest $request, Schedule $schedule)
    {
        $validated = $request->validated();
        $this->validateTeacherSubjectPair($validated['teacher_id'], $validated['subject_id']);
        $this->validateConflicts($validated, $schedule->id);

        $schedule->update($validated);

        return new ScheduleResource($schedule->fresh()->load(['academicYear', 'classroom', 'teacher', 'subject']));
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();

        return response()->json(['message' => 'Jadwal pelajaran berhasil dihapus']);
    }

    protected function validateTeacherSubjectPair(int $teacherId, int $subjectId): void
    {
        $exists = TeacherSubject::query()
            ->where('teacher_id', $teacherId)
            ->where('subject_id', $subjectId)
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'subject_id' => 'Mata pelajaran tidak sesuai dengan guru yang dipilih.',
            ]);
        }
    }

    protected function validateConflicts(array $validated, ?int $ignoreId = null): void
    {
        $overlapQuery = Schedule::query()
            ->where('hari', $validated['hari'])
            ->where(function ($query) use ($validated) {
                $query
                    ->where('jam_mulai', '<', $validated['jam_selesai'])
                    ->where('jam_selesai', '>', $validated['jam_mulai']);
            });

        if ($ignoreId) {
            $overlapQuery->whereKeyNot($ignoreId);
        }

        $classroomConflict = (clone $overlapQuery)
            ->where('kelas_id', $validated['kelas_id'])
            ->exists();

        if ($classroomConflict) {
            throw ValidationException::withMessages([
                'kelas_id' => 'Kelas sudah memiliki jadwal lain pada hari dan jam tersebut.',
            ]);
        }

        $teacherConflict = (clone $overlapQuery)
            ->where('teacher_id', $validated['teacher_id'])
            ->exists();

        if ($teacherConflict) {
            throw ValidationException::withMessages([
                'teacher_id' => 'Guru sudah memiliki jadwal lain pada hari dan jam tersebut.',
            ]);
        }
    }

    protected function dayOrderSql(): string
    {
        return "CASE jadwal_pelajarans.hari
            WHEN 'Senin' THEN 1
            WHEN 'Selasa' THEN 2
            WHEN 'Rabu' THEN 3
            WHEN 'Kamis' THEN 4
            WHEN 'Jumat' THEN 5
            WHEN 'Sabtu' THEN 6
            ELSE 7
        END";
    }

    protected function authorizeGuruScheduleAccess(Request $request, Schedule $schedule): void
    {
        $user = $request->user();

        if (! $user || $user->role !== 'guru') {
            return;
        }

        $teacherId = optional($user->loadMissing('teacher')->teacher)->id;

        if (! $teacherId || $schedule->teacher_id !== $teacherId) {
            abort(403, 'Anda tidak memiliki akses.');
        }
    }
}
