<?php

namespace App\Http\Requests\Attendance;

use App\Enums\AttendanceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkStoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal' => ['required', 'date'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'guru_id' => ['nullable', 'exists:teachers,id'],
            'attendances' => ['required', 'array', 'min:1'],
            'attendances.*.siswa_id' => ['required', 'exists:students,id'],
            'attendances.*.status' => ['required', Rule::in([
                AttendanceStatus::HADIR,
                AttendanceStatus::IZIN,
                AttendanceStatus::SAKIT,
                AttendanceStatus::ALFA,
            ])],
            'attendances.*.keterangan' => ['nullable', 'string'],
        ];
    }
}
