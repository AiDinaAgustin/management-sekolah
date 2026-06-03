<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClassroomResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'tingkat' => $this->tingkat,
            'rombel' => $this->rombel,
            'nama_kelas' => $this->nama_kelas,
            'wali_kelas_id' => $this->wali_kelas_id,
            'tahun_ajaran_id' => $this->tahun_ajaran_id,
            'jumlah_siswa' => isset($this->students_count) ? $this->students_count : null,
            'wali_kelas' => $this->whenLoaded('waliKelas', function () {
                if (! $this->waliKelas) {
                    return null;
                }

                return [
                    'id' => $this->waliKelas->id,
                    'nama' => $this->waliKelas->nama,
                    'nip' => $this->waliKelas->nip,
                    'email' => $this->waliKelas->email,
                ];
            }),
            'tahun_ajaran' => $this->whenLoaded('academicYear', function () {
                if (! $this->academicYear) {
                    return null;
                }

                return [
                    'id' => $this->academicYear->id,
                    'nama_tahun_ajaran' => $this->academicYear->nama_tahun_ajaran,
                    'semester' => $this->academicYear->semester,
                    'status_aktif' => (bool) $this->academicYear->status_aktif,
                ];
            }),
            'created_at' => optional($this->created_at)->toDateTimeString(),
            'updated_at' => optional($this->updated_at)->toDateTimeString(),
        ];
    }
}
