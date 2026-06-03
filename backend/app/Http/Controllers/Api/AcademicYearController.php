<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcademicYear\StoreAcademicYearRequest;
use App\Http\Requests\AcademicYear\UpdateAcademicYearRequest;
use App\Http\Resources\AcademicYearResource;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AcademicYearController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $perPage = (int) $request->get('per_page', 10);
        $perPage = $perPage > 0 ? min($perPage, 100) : 10;
        $allowedSorts = [
            'nama_tahun_ajaran' => 'nama_tahun_ajaran',
            'semester' => 'semester',
            'status_aktif' => 'status_aktif',
        ];
        $sortBy = $request->get('sort_by', 'status_aktif');
        $sortDirection = strtolower((string) $request->get('sort_direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        if (! array_key_exists($sortBy, $allowedSorts)) {
            $sortBy = 'status_aktif';
        }

        $query = AcademicYear::query()
            ->select(['id', 'nama_tahun_ajaran', 'semester', 'status_aktif', 'created_at', 'updated_at'])
            ->withCount('classrooms')
            ->when($user && $user->role === 'guru', function ($builder) {
                $builder->where('status_aktif', true);
            })
            ->when($request->filled('search'), function ($builder) use ($request) {
                $search = $request->get('search');
                $builder->where('nama_tahun_ajaran', 'ilike', '%'.$search.'%');
            })
            ->when($request->filled('semester'), function ($builder) use ($request) {
                $builder->where('semester', $request->get('semester'));
            })
            ->when($request->filled('status_aktif'), function ($builder) use ($request) {
                $builder->where('status_aktif', filter_var($request->get('status_aktif'), FILTER_VALIDATE_BOOLEAN));
            })
            ->orderBy($allowedSorts[$sortBy], $sortDirection)
            ->orderByDesc('id');

        if ($request->boolean('simple')) {
            return response()->json([
                'data' => $query->get(['id', 'nama_tahun_ajaran', 'semester', 'status_aktif']),
            ]);
        }

        return AcademicYearResource::collection(
            $query->paginate($perPage)->appends($request->query())
        );
    }

    public function store(StoreAcademicYearRequest $request)
    {
        $academicYear = DB::transaction(function () use ($request) {
            $validated = $request->validated();

            if ($validated['status_aktif']) {
                AcademicYear::query()->update(['status_aktif' => false]);
            }

            return AcademicYear::create($validated);
        });

        return (new AcademicYearResource($academicYear->loadCount('classrooms')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(AcademicYear $academic_year)
    {
        return new AcademicYearResource($academic_year->loadCount('classrooms'));
    }

    public function update(UpdateAcademicYearRequest $request, AcademicYear $academic_year)
    {
        DB::transaction(function () use ($request, $academic_year) {
            $validated = $request->validated();

            if ($validated['status_aktif']) {
                AcademicYear::query()
                    ->whereKeyNot($academic_year->id)
                    ->update(['status_aktif' => false]);
            }

            $academic_year->update($validated);
        });

        return new AcademicYearResource($academic_year->fresh()->loadCount('classrooms'));
    }

    public function destroy(AcademicYear $academic_year)
    {
        $academic_year->delete();

        return response()->json(['message' => 'Data tahun ajaran berhasil dihapus']);
    }
}
