<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'tahun_ajaran_id' => $this->tahun_ajaran_id,
            'kelas_id' => $this->kelas_id,
            'teacher_id' => $this->teacher_id,
            'subject_id' => $this->subject_id,
            'hari' => $this->hari,
            'jam_mulai' => $this->jam_mulai,
            'jam_selesai' => $this->jam_selesai,
            'ruangan' => $this->ruangan,
            'tahun_ajaran' => $this->whenLoaded('academicYear', function () {
                return [
                    'id' => $this->academicYear->id,
                    'nama_tahun_ajaran' => $this->academicYear->nama_tahun_ajaran,
                    'semester' => $this->academicYear->semester,
                ];
            }),
            'kelas' => $this->whenLoaded('classroom', function () {
                return [
                    'id' => $this->classroom->id,
                    'nama_kelas' => $this->classroom->nama_kelas,
                ];
            }),
            'teacher' => $this->whenLoaded('teacher', function () {
                return [
                    'id' => $this->teacher->id,
                    'nama' => $this->teacher->nama,
                    'nip' => $this->teacher->nip,
                ];
            }),
            'subject' => $this->whenLoaded('subject', function () {
                return [
                    'id' => $this->subject->id,
                    'kode_mapel' => $this->subject->kode_mapel,
                    'nama_mapel' => $this->subject->nama_mapel,
                ];
            }),
            'created_at' => optional($this->created_at)->toDateTimeString(),
            'updated_at' => optional($this->updated_at)->toDateTimeString(),
        ];
    }
}
