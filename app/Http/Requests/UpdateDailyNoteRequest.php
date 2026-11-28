<?php

namespace App\Http\Requests;

use App\Models\DailyNote;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateDailyNoteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'student_id' => ['sometimes', 'nullable', 'integer', 'exists:students,id'],
            'section_id' => ['sometimes', 'nullable', 'integer', 'exists:sections,id'],
            'employee_id' => ['sometimes', 'nullable', 'integer', 'exists:employees,id'],
            'academic_year_id' => ['sometimes', 'nullable', 'integer', 'exists:academic_years,id'],
            'date' => ['sometimes', 'required', 'date'],
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'content' => ['sometimes', 'required', 'string'],
            'category' => ['sometimes', 'nullable', 'string', 'max:100'],
            'status' => ['sometimes', 'nullable', 'string', 'max:100'],
        ];
    }

    /**
     * Configure the validator instance to ensure note remains linked to a student or section.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var DailyNote|null $dailyNote */
            $dailyNote = $this->route('daily_note');

            if (! $dailyNote instanceof DailyNote) {
                return;
            }

            $studentId = $this->exists('student_id') ? $this->input('student_id') : $dailyNote->student_id;
            $sectionId = $this->exists('section_id') ? $this->input('section_id') : $dailyNote->section_id;

            if (empty($studentId) && empty($sectionId)) {
                $validator->errors()->add('student_id', 'Catatan harian harus terhubung dengan siswa atau rombel.');
            }
        });
    }
}
