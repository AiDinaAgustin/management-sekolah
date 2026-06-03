<?php

namespace App\Http\Requests\Subject;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $subject = $this->route('subject');
        $subjectId = $subject ? $subject->id : null;

        return [
            'kode_mapel' => ['required', 'string', 'max:255', Rule::unique('mata_pelajarans', 'kode_mapel')->ignore($subjectId)],
            'nama_mapel' => ['required', 'string', 'max:255'],
        ];
    }
}
