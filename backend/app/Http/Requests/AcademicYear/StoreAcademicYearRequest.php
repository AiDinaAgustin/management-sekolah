<?php

namespace App\Http\Requests\AcademicYear;

use Illuminate\Foundation\Http\FormRequest;

class StoreAcademicYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_tahun_ajaran' => ['required', 'string', 'max:255'],
            'semester' => ['required', 'string', 'in:Ganjil,Genap'],
            'status_aktif' => ['required', 'boolean'],
        ];
    }
}
