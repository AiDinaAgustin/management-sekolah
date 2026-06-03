<?php

namespace App\Http\Requests\TeacherSubject;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeacherSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'teacher_id' => [
                'required',
                'exists:teachers,id',
                Rule::unique('teacher_subjects')->where(function ($query) {
                    return $query->where('subject_id', $this->input('subject_id'));
                }),
            ],
            'subject_id' => ['required', 'exists:mata_pelajarans,id'],
        ];
    }
}
