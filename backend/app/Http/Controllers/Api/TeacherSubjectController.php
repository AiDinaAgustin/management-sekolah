<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TeacherSubject\StoreTeacherSubjectRequest;
use App\Http\Requests\TeacherSubject\UpdateTeacherSubjectRequest;
use App\Http\Resources\TeacherSubjectResource;
use App\Models\TeacherSubject;
use Illuminate\Http\Request;

class TeacherSubjectController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        $perPage = $perPage > 0 ? min($perPage, 100) : 10;
        $allowedSorts = [
            'teacher' => 'teachers.nama',
            'subject' => 'mata_pelajarans.nama_mapel',
            'kode_mapel' => 'mata_pelajarans.kode_mapel',
        ];
        $sortBy = $request->get('sort_by', 'teacher');
        $sortDirection = strtolower((string) $request->get('sort_direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        if (! array_key_exists($sortBy, $allowedSorts)) {
            $sortBy = 'teacher';
        }

        $teacherSubjects = TeacherSubject::query()
            ->select('teacher_subjects.*')
            ->with(['teacher', 'subject'])
            ->leftJoin('teachers', 'teachers.id', '=', 'teacher_subjects.teacher_id')
            ->leftJoin('mata_pelajarans', 'mata_pelajarans.id', '=', 'teacher_subjects.subject_id')
            ->when($request->filled('search'), function ($builder) use ($request) {
                $search = $request->get('search');
                $builder->where(function ($query) use ($search) {
                    $query
                        ->where('teachers.nama', 'ilike', '%'.$search.'%')
                        ->orWhere('teachers.nip', 'ilike', '%'.$search.'%')
                        ->orWhere('mata_pelajarans.nama_mapel', 'ilike', '%'.$search.'%')
                        ->orWhere('mata_pelajarans.kode_mapel', 'ilike', '%'.$search.'%');
                });
            })
            ->when($request->filled('teacher_id'), function ($builder) use ($request) {
                $builder->where('teacher_subjects.teacher_id', $request->get('teacher_id'));
            })
            ->when($request->filled('subject_id'), function ($builder) use ($request) {
                $builder->where('teacher_subjects.subject_id', $request->get('subject_id'));
            })
            ->orderBy($allowedSorts[$sortBy], $sortDirection)
            ->orderBy('teachers.nama')
            ->paginate($perPage)
            ->appends($request->query());

        return TeacherSubjectResource::collection($teacherSubjects);
    }

    public function store(StoreTeacherSubjectRequest $request)
    {
        $teacherSubject = TeacherSubject::create($request->validated());

        return (new TeacherSubjectResource($teacherSubject->load(['teacher', 'subject'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(TeacherSubject $teacher_subject)
    {
        return new TeacherSubjectResource($teacher_subject->load(['teacher', 'subject']));
    }

    public function update(UpdateTeacherSubjectRequest $request, TeacherSubject $teacher_subject)
    {
        $teacher_subject->update($request->validated());

        return new TeacherSubjectResource($teacher_subject->fresh()->load(['teacher', 'subject']));
    }

    public function destroy(TeacherSubject $teacher_subject)
    {
        $teacher_subject->delete();

        return response()->json(['message' => 'Relasi guru mapel berhasil dihapus']);
    }
}
