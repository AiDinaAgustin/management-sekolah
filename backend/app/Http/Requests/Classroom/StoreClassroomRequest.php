<?php

namespace App\Http\Requests\Classroom;

use Illuminate\Foundation\Http\FormRequest;

class StoreClassroomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tingkat' => ['required', 'integer', 'min:1', 'max:12'],
            'rombel' => ['required', 'string', 'max:10'],
            'nama_kelas' => ['required', 'string', 'max:255'],
            'wali_kelas_id' => ['nullable', 'exists:teachers,id'],
            'tahun_ajaran_id' => ['required', 'exists:tahun_ajarans,id'],
        ];
    }
}
