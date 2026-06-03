<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AcademicYearResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'nama_tahun_ajaran' => $this->nama_tahun_ajaran,
            'semester' => $this->semester,
            'status_aktif' => (bool) $this->status_aktif,
            'jumlah_kelas' => isset($this->classrooms_count) ? $this->classrooms_count : null,
            'created_at' => optional($this->created_at)->toDateTimeString(),
            'updated_at' => optional($this->updated_at)->toDateTimeString(),
        ];
    }
}
