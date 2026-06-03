<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreStudentRequest;
use App\Http\Requests\Student\UpdateStudentRequest;
use App\Http\Resources\StudentResource;
use App\Models\ParentModel;
use App\Models\Schedule;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $perPage = (int) $request->get('per_page', 10);
        $perPage = $perPage > 0 ? min($perPage, 100) : 10;
        $allowedSorts = [
            'nama_lengkap' => 'students.nama_lengkap',
            'jenis_kelamin' => 'students.jenis_kelamin',
            'tanggal_lahir' => 'students.tanggal_lahir',
            'kelas' => 'kelas.nama_kelas',
        ];
        $sortBy = $request->get('sort_by', 'nama_lengkap');
        $sortDirection = strtolower((string) $request->get('sort_direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        if (! array_key_exists($sortBy, $allowedSorts)) {
            $sortBy = 'nama_lengkap';
        }

        $students = Student::query()
            ->select('students.*')
            ->with(['classroom', 'parent'])
            ->leftJoin('kelas', 'kelas.id', '=', 'students.kelas_id')
            ->when($user && $user->role === 'guru' && ! $request->boolean('for_attendance'), function ($query) use ($user) {
                $teacherId = optional($user->loadMissing('teacher')->teacher)->id;

                if (! $teacherId) {
                    $query->whereRaw('1 = 0');
                    return;
                }

                $scheduleClassroomIds = Schedule::query()
                    ->where('teacher_id', $teacherId)
                    ->pluck('kelas_id');

                $waliClassroomIds = DB::table('kelas')
                    ->where('wali_kelas_id', $teacherId)
                    ->pluck('id');

                $classroomIds = $scheduleClassroomIds
                    ->merge($waliClassroomIds)
                    ->unique()
                    ->values();

                $query->whereIn('students.kelas_id', $classroomIds);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->get('search');

                $query->where(function ($studentQuery) use ($search) {
                    $studentQuery
                        ->where('nama_lengkap', 'ilike', '%'.$search.'%')
                        ->orWhere('nis', 'ilike', '%'.$search.'%');
                });
            })
            ->when($request->filled('kelas_id'), function ($query) use ($request) {
                $query->where('kelas_id', $request->get('kelas_id'));
            })
            ->when($request->filled('status_aktif'), function ($query) use ($request) {
                $query->where('status_aktif', filter_var($request->get('status_aktif'), FILTER_VALIDATE_BOOLEAN));
            })
            ->orderBy($allowedSorts[$sortBy], $sortDirection)
            ->orderBy('students.nama_lengkap')
            ->paginate($perPage)
            ->appends($request->query());

        return StudentResource::collection($students);
    }

    public function store(StoreStudentRequest $request)
    {
        $student = DB::transaction(function () use ($request) {
            $validated = $request->validated();
            $parent = ParentModel::create($this->extractParentData($validated));
            $studentData = $this->extractStudentData($validated);
            $studentData['orang_tua_id'] = $parent->id;

            return Student::create($studentData);
        });

        return (new StudentResource($student->fresh()->load(['classroom', 'parent'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Student $student)
    {
        return new StudentResource($student->load(['classroom', 'parent']));
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        DB::transaction(function () use ($request, $student) {
            $validated = $request->validated();
            $parentData = $this->extractParentData($validated);
            $parent = $student->parent;

            if ($parent) {
                $parent->update($parentData);
            } else {
                $parent = ParentModel::create($parentData);
                $student->orang_tua_id = $parent->id;
            }

            $studentData = $this->extractStudentData($validated);
            $studentData['orang_tua_id'] = $parent->id;
            $student->update($studentData);
        });

        return new StudentResource($student->fresh()->load(['classroom', 'parent']));
    }

    public function destroy(Student $student)
    {
        $student->delete();

        return response()->json(['message' => 'Data siswa berhasil dihapus']);
    }

    protected function extractStudentData(array $validated): array
    {
        return collect($validated)
            ->except([
                'nama_ayah',
                'no_hp_ayah',
                'pekerjaan_ayah',
                'nama_ibu',
                'no_hp_ibu',
                'pekerjaan_ibu',
                'alamat_orang_tua',
            ])
            ->all();
    }

    protected function extractParentData(array $validated): array
    {
        return [
            'nama_ayah' => $validated['nama_ayah'],
            'no_hp_ayah' => $validated['no_hp_ayah'] ?? null,
            'pekerjaan_ayah' => $validated['pekerjaan_ayah'] ?? null,
            'nama_ibu' => $validated['nama_ibu'],
            'no_hp_ibu' => $validated['no_hp_ibu'] ?? null,
            'pekerjaan_ibu' => $validated['pekerjaan_ibu'] ?? null,
            'alamat' => $validated['alamat_orang_tua'] ?? null,
        ];
    }
}
