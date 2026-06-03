<?php

namespace App\Http\Requests\TeacherSubject;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeacherSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $teacherSubject = $this->route('teacher_subject');
        $teacherSubjectId = $teacherSubject ? $teacherSubject->id : null;

        return [
            'teacher_id' => [
                'required',
                'exists:teachers,id',
                Rule::unique('teacher_subjects')
                    ->ignore($teacherSubjectId)
                    ->where(function ($query) {
                        return $query->where('subject_id', $this->input('subject_id'));
                    }),
            ],
            'subject_id' => ['required', 'exists:mata_pelajarans,id'],
        ];
    }
}
