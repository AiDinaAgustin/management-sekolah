<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreTeacherRequest;
use App\Http\Requests\Teacher\UpdateTeacherRequest;
use App\Http\Resources\TeacherResource;
use App\Models\User;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user && $user->role === 'guru') {
            $teacher = optional($user->loadMissing('teacher')->teacher);

            return response()->json([
                'data' => $teacher ? [[
                    'id' => $teacher->id,
                    'nama' => $teacher->nama,
                    'nip' => $teacher->nip,
                    'email' => $teacher->email,
                ]] : [],
            ]);
        }

        if ($request->boolean('simple')) {
            $query = Teacher::query()
                ->select(['id', 'nama', 'nip', 'email'])
                ->orderBy('nama');

            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where(function ($builder) use ($search) {
                    $builder
                        ->where('nama', 'ilike', '%'.$search.'%')
                        ->orWhere('nip', 'ilike', '%'.$search.'%');
                });
            }

            return response()->json([
                'data' => $query->get(),
            ]);
        }

        $perPage = (int) $request->get('per_page', 10);
        $perPage = $perPage > 0 ? min($perPage, 100) : 10;
        $allowedSorts = [
            'nama' => 'nama',
            'nip' => 'nip',
            'email' => 'email',
        ];
        $sortBy = $request->get('sort_by', 'nama');
        $sortDirection = strtolower((string) $request->get('sort_direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        if (! array_key_exists($sortBy, $allowedSorts)) {
            $sortBy = 'nama';
        }

        $query = Teacher::query()
            ->select(['id', 'user_id', 'nama', 'nip', 'email', 'no_hp', 'alamat', 'created_at', 'updated_at'])
            ->withCount('classrooms');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('nama', 'ilike', '%'.$search.'%')
                    ->orWhere('nip', 'ilike', '%'.$search.'%')
                    ->orWhere('email', 'ilike', '%'.$search.'%');
            });
        }

        $teachers = $query
            ->orderBy($allowedSorts[$sortBy], $sortDirection)
            ->orderBy('nama')
            ->paginate($perPage)
            ->appends($request->query());

        return TeacherResource::collection($teachers);
    }

    public function store(StoreTeacherRequest $request)
    {
        $teacher = DB::transaction(function () use ($request) {
            $validated = $request->validated();

            $user = User::create([
                'name' => $validated['nama'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['nip']),
                'role' => 'guru',
            ]);

            $validated['user_id'] = $user->id;

            return Teacher::create($validated);
        });

        return (new TeacherResource($teacher->loadCount('classrooms')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Teacher $teacher)
    {
        return new TeacherResource($teacher->loadCount('classrooms'));
    }

    public function update(UpdateTeacherRequest $request, Teacher $teacher)
    {
        DB::transaction(function () use ($request, $teacher) {
            $validated = $request->validated();

            if ($teacher->user_id) {
                $teacher->user()->update([
                    'name' => $validated['nama'],
                    'email' => $validated['email'],
                ]);
            } else {
                $user = User::create([
                    'name' => $validated['nama'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['nip']),
                    'role' => 'guru',
                ]);

                $validated['user_id'] = $user->id;
            }

            $teacher->update($validated);
        });

        return new TeacherResource($teacher->fresh()->loadCount('classrooms'));
    }

    public function destroy(Teacher $teacher)
    {
        DB::transaction(function () use ($teacher) {
            $user = $teacher->user;
            $teacher->delete();

            if ($user) {
                $user->delete();
            }
        });

        return response()->json(['message' => 'Data guru berhasil dihapus']);
    }
}
