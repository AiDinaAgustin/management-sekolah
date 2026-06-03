<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Subject\StoreSubjectRequest;
use App\Http\Requests\Subject\UpdateSubjectRequest;
use App\Http\Resources\SubjectResource;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        if ($request->boolean('simple')) {
            $query = Subject::query()
                ->select(['id', 'kode_mapel', 'nama_mapel'])
                ->orderBy('nama_mapel');

            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where(function ($builder) use ($search) {
                    $builder
                        ->where('nama_mapel', 'ilike', '%'.$search.'%')
                        ->orWhere('kode_mapel', 'ilike', '%'.$search.'%');
                });
            }

            return response()->json([
                'data' => $query->get(),
            ]);
        }

        $perPage = (int) $request->get('per_page', 10);
        $perPage = $perPage > 0 ? min($perPage, 100) : 10;
        $allowedSorts = [
            'nama_mapel' => 'nama_mapel',
            'kode_mapel' => 'kode_mapel',
        ];
        $sortBy = $request->get('sort_by', 'nama_mapel');
        $sortDirection = strtolower((string) $request->get('sort_direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        if (! array_key_exists($sortBy, $allowedSorts)) {
            $sortBy = 'nama_mapel';
        }

        $subjects = Subject::query()
            ->select(['id', 'kode_mapel', 'nama_mapel', 'created_at', 'updated_at'])
            ->when($request->filled('search'), function ($builder) use ($request) {
                $search = $request->get('search');
                $builder->where(function ($query) use ($search) {
                    $query
                        ->where('nama_mapel', 'ilike', '%'.$search.'%')
                        ->orWhere('kode_mapel', 'ilike', '%'.$search.'%');
                });
            })
            ->orderBy($allowedSorts[$sortBy], $sortDirection)
            ->orderBy('nama_mapel')
            ->paginate($perPage)
            ->appends($request->query());

        return SubjectResource::collection($subjects);
    }

    public function store(StoreSubjectRequest $request)
    {
        $subject = Subject::create($request->validated());

        return (new SubjectResource($subject))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Subject $subject)
    {
        return new SubjectResource($subject);
    }

    public function update(UpdateSubjectRequest $request, Subject $subject)
    {
        $subject->update($request->validated());

        return new SubjectResource($subject->fresh());
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();

        return response()->json(['message' => 'Data mata pelajaran berhasil dihapus']);
    }
}
