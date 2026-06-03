<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Classroom\StoreClassroomRequest;
use App\Http\Requests\Classroom\UpdateClassroomRequest;
use App\Http\Resources\ClassroomResource;
use App\Models\Classroom;
use App\Models\Schedule;
use Illuminate\Http\Request;

class ClassroomController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $perPage = (int) $request->get('per_page', 10);
        $perPage = $perPage > 0 ? min($perPage, 100) : 10;
        $allowedSorts = [
            'nama_kelas' => 'kelas.nama_kelas',
            'tingkat' => 'kelas.tingkat',
            'rombel' => 'kelas.rombel',
            'wali_kelas' => 'teachers.nama',
            'tahun_ajaran' => 'tahun_ajarans.nama_tahun_ajaran',
        ];
        $sortBy = $request->get('sort_by', 'nama_kelas');
        $sortDirection = strtolower((string) $request->get('sort_direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        if (! array_key_exists($sortBy, $allowedSorts)) {
            $sortBy = 'nama_kelas';
        }

        $query = Classroom::query()
            ->select('kelas.*')
            ->with(['waliKelas', 'academicYear'])
            ->withCount('students')
            ->leftJoin('teachers', 'teachers.id', '=', 'kelas.wali_kelas_id')
            ->leftJoin('tahun_ajarans', 'tahun_ajarans.id', '=', 'kelas.tahun_ajaran_id')
            ->when($user && $user->role === 'guru' && ! $request->boolean('for_attendance'), function ($builder) use ($user) {
                $teacherId = optional($user->loadMissing('teacher')->teacher)->id;

                if (! $teacherId) {
                    $builder->whereRaw('1 = 0');
                    return;
                }

                $classroomIds = Schedule::query()
                    ->where('teacher_id', $teacherId)
                    ->pluck('kelas_id');

                $builder->where(function ($query) use ($teacherId, $classroomIds) {
                    $query
                        ->where('kelas.wali_kelas_id', $teacherId)
                        ->orWhereIn('kelas.id', $classroomIds);
                });
            })
            ->when($request->filled('search'), function ($builder) use ($request) {
                $search = $request->get('search');

                $builder->where(function ($query) use ($search) {
                    $query
                        ->where('kelas.nama_kelas', 'ilike', '%'.$search.'%')
                        ->orWhere('kelas.rombel', 'ilike', '%'.$search.'%');
                });
            })
            ->when($request->filled('tingkat'), function ($builder) use ($request) {
                $builder->where('kelas.tingkat', $request->get('tingkat'));
            })
            ->when($request->filled('tahun_ajaran_id'), function ($builder) use ($request) {
                $builder->where('kelas.tahun_ajaran_id', $request->get('tahun_ajaran_id'));
            })
            ->orderBy($allowedSorts[$sortBy], $sortDirection)
            ->orderBy('tingkat')
            ->orderBy('rombel');

        if ($request->boolean('simple')) {
            return response()->json([
                'data' => $query->get(),
            ]);
        }

        $classrooms = $query
            ->paginate($perPage)
            ->appends($request->query());

        return ClassroomResource::collection($classrooms);
    }

    public function store(StoreClassroomRequest $request)
    {
        $classroom = Classroom::create($request->validated());

        return (new ClassroomResource($classroom->load(['waliKelas', 'academicYear'])->loadCount('students')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Classroom $class)
    {
        return new ClassroomResource($class->load(['waliKelas', 'academicYear'])->loadCount('students'));
    }

    public function update(UpdateClassroomRequest $request, Classroom $class)
    {
        $class->update($request->validated());

        return new ClassroomResource($class->fresh()->load(['waliKelas', 'academicYear'])->loadCount('students'));
    }

    public function destroy(Classroom $class)
    {
        $class->delete();

        return response()->json(['message' => 'Data kelas berhasil dihapus']);
    }
}
