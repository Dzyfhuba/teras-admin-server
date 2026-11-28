<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDailyNoteRequest extends FormRequest
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
            'student_id' => ['nullable', 'integer', 'exists:students,id', 'required_without:section_id'],
            'section_id' => ['nullable', 'integer', 'exists:sections,id', 'required_without:student_id'],
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'academic_year_id' => ['nullable', 'integer', 'exists:academic_years,id'],
            'date' => ['required', 'date'],
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'max:100'],
        ];
    }
}
