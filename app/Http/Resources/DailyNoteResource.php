<?php

namespace App\Http\Resources;

use App\Models\DailyNote;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin DailyNote
 */
class DailyNoteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'section_id' => $this->section_id,
            'employee_id' => $this->employee_id,
            'academic_year_id' => $this->academic_year_id,
            'date' => $this->date?->toDateString(),
            'title' => $this->title,
            'content' => $this->content,
            'category' => $this->category,
            'status' => $this->status,
            'student' => $this->whenLoaded('student', fn (): ?array => $this->student ? [
                'id' => $this->student->id,
                'nama_lengkap' => $this->student->nama_lengkap,
                'nis' => $this->student->nis,
            ] : null),
            'section' => $this->whenLoaded('section', fn (): ?array => $this->section ? [
                'id' => $this->section->id,
                'grade' => $this->section->grade,
                'name' => $this->section->name,
            ] : null),
            'employee' => $this->whenLoaded('employee', fn (): ?array => $this->employee ? [
                'id' => $this->employee->id,
                'nama_lengkap' => $this->employee->nama_lengkap,
                'jabatan' => $this->employee->jabatan,
            ] : null),
            'academic_year' => $this->whenLoaded('academicYear', fn (): ?array => $this->academicYear ? [
                'id' => $this->academicYear->id,
                'name' => $this->academicYear->name,
                'is_active' => $this->academicYear->is_active,
            ] : null),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
