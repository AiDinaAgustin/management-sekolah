<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SchoolProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SchoolProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $profile = SchoolProfile::query()->latest('id')->first();
        $namaSekolah = $profile ? $profile->nama_sekolah : 'School Admin';
        $tagline = $profile ? $profile->tagline : 'Sistem Informasi Sekolah';

        return response()->json([
            'data' => [
                'nama_sekolah' => $namaSekolah,
                'tagline' => $tagline,
            ],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama_sekolah' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
        ]);

        $profile = SchoolProfile::query()->latest('id')->first();

        if ($profile) {
            $profile->update($validated);
        } else {
            $profile = SchoolProfile::query()->create($validated);
        }

        return response()->json([
            'message' => 'Profil sekolah berhasil diperbarui.',
            'data' => [
                'id' => $profile->id,
                'nama_sekolah' => $profile->nama_sekolah,
                'tagline' => $profile->tagline,
            ],
        ]);
    }
}
