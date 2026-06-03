<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'siswa_id' => $this->siswa_id,
            'tanggal' => optional($this->tanggal)->format('Y-m-d'),
            'status' => $this->status,
            'keterangan' => $this->keterangan,
            'guru_id' => $this->guru_id,
            'student' => $this->whenLoaded('student', function () {
                return [
                    'id' => $this->student->id,
                    'nis' => $this->student->nis,
                    'nama_lengkap' => $this->student->nama_lengkap,
                    'kelas_id' => $this->student->kelas_id,
                    'kelas' => $this->student->relationLoaded('classroom') && $this->student->classroom ? [
                        'id' => $this->student->classroom->id,
                        'nama_kelas' => $this->student->classroom->nama_kelas,
                    ] : null,
                ];
            }),
            'teacher' => $this->whenLoaded('teacher', function () {
                return [
                    'id' => $this->teacher->id,
                    'nama' => $this->teacher->nama,
                    'nip' => $this->teacher->nip,
                ];
            }),
            'created_at' => optional($this->created_at)->toDateTimeString(),
            'updated_at' => optional($this->updated_at)->toDateTimeString(),
        ];
    }
}
