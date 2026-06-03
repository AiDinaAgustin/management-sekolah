<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'nis' => $this->nis,
            'nama_lengkap' => $this->nama_lengkap,
            'jenis_kelamin' => $this->jenis_kelamin,
            'tempat_lahir' => $this->tempat_lahir,
            'tanggal_lahir' => $this->tanggal_lahir,
            'alamat' => $this->alamat,
            'kelas_id' => $this->kelas_id,
            'orang_tua_id' => $this->orang_tua_id,
            'status_aktif' => (bool) $this->status_aktif,
            'kelas' => $this->whenLoaded('classroom', function () {
                return [
                    'id' => $this->classroom->id,
                    'nama_kelas' => $this->classroom->nama_kelas,
                ];
            }),
            'orang_tua' => $this->whenLoaded('parent', function () {
                return [
                    'id' => $this->parent->id,
                    'nama_ayah' => $this->parent->nama_ayah,
                    'no_hp_ayah' => $this->parent->no_hp_ayah,
                    'pekerjaan_ayah' => $this->parent->pekerjaan_ayah,
                    'nama_ibu' => $this->parent->nama_ibu,
                    'no_hp_ibu' => $this->parent->no_hp_ibu,
                    'pekerjaan_ibu' => $this->parent->pekerjaan_ibu,
                    'alamat' => $this->parent->alamat,
                ];
            }),
            'created_at' => optional($this->created_at)->toDateTimeString(),
            'updated_at' => optional($this->updated_at)->toDateTimeString(),
        ];
    }
}
