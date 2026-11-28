<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Section;
use App\Models\StudentSection;
use Illuminate\Http\Request;
use App\Models\Promotion;
use App\Models\PromotionItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PromotionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() {
        $promotions = Promotion::with([
            'sourceAcademicYear',
            'targetAcademicYear',
            'sourceSection',
            'employee',
            'items.student',
            'items.targetSection',
        ])
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Riwayat keputusan kenaikan siswa berhasil diambil.',
            'data' => $promotions,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        $validated = $request->validate([
            'source_academic_year_id' => 'required|integer|exists:academic_years,id',
            'target_academic_year_id' => 'required|integer|different:source_academic_year_id|exists:academic_years,id',
            'source_section_id' => 'nullable|integer|exists:sections,id',
            'employee_id' => 'nullable|integer|exists:employees,id',
            'notes' => 'nullable|string',
            // Data keputusan setiap siswa
            'students' => 'required|array|min:1',
            'students.*.student_id' => 'required|integer|exists:students,id',
            'students.*.source_student_section_id' => 'nullable|integer|exists:student_sections,id',
            'students.*.action' => 'required|in:naik,tinggal kelas,lulus,mutasi keluar,mutasi masuk',
            'students.*.target_section_id' => 'nullable|integer|exists:sections,id',
        ]);
        foreach ($validated['students'] as $index => $studentData) {
            $action = $studentData['action'];
            $targetSectionId = $studentData['target_section_id'] ?? null;
            if (in_array($action, ['naik', 'tinggal kelas', 'mutasi masuk']) && !$targetSectionId) {
                throw ValidationException::withMessages([
                    "students.$index.target_section_id" =>
                        'Kelas tujuan wajib diisi untuk keputusan ini.',
                ]);
            }
            if (in_array($action, ['lulus', 'mutasi keluar']) && $targetSectionId) {
                throw ValidationException::withMessages([
                    "students.$index.target_section_id" =>
                        'Kelas tujuan tidak diperlukan untuk keputusan ini.',
                ]);
            }

            if ($targetSectionId) {
                $targetSection = Section::findOrFail($targetSectionId);
                if ($targetSection->academic_year_id != $validated['target_academic_year_id']) {
                    throw ValidationException::withMessages([
                        "students.$index.target_section_id" =>
                        'Kelas tujuan harus berasal dari tahun ajaran tujuan.',
                    ]);
                }
            }

            $sourceStudentSection = null;

            if ($action !== 'mutasi masuk') {
                $sourceStudentSection = StudentSection::with('section')
                    ->where('id', $studentData['source_student_section_id'] ?? null)
                    ->where('student_id', $studentData['student_id'])
                    ->where('status', 'aktif')
                    ->first();

                if (
                    !$sourceStudentSection ||
                    !$sourceStudentSection->section ||
                    $sourceStudentSection->section->academic_year_id != $validated['source_academic_year_id']
                ) {
                    throw ValidationException::withMessages([
                        "students.$index.student_id" =>
                            'Data siswa tidak sesuai dengan tahun ajaran asal.',
                    ]);
                }

                if (
                    !empty($validated['source_section_id']) &&
                    $sourceStudentSection->section_id != $validated['source_section_id']
                ) {
                    throw ValidationException::withMessages([
                        "students.$index.student_id" =>
                            'Siswa tidak terdaftar di kelas asal yang dipilih.',
                    ]);
                }
            }
            if (
                $action === 'lulus' &&
                $sourceStudentSection &&
                $sourceStudentSection->section->grade != 6
            ) {
                throw ValidationException::withMessages([
                    "students.$index.action" =>
                        'Siswa hanya dapat dinyatakan lulus jika berada di kelas 6.',
                ]);
            }

            if (
                in_array($action, ['naik', 'tinggal kelas']) &&
                $sourceStudentSection
            ) {
                $sourceGrade = $sourceStudentSection->section->grade;
                $targetGrade = $targetSection->grade;

                if ($action === 'naik') {
                    if ($sourceGrade >= 6 || $targetGrade != $sourceGrade + 1) {
                        throw ValidationException::withMessages([
                            "students.$index.target_section_id" =>
                                'Siswa hanya dapat naik ke tingkat kelas berikutnya.',
                        ]);
                    }
                }

                if ($action === 'tinggal kelas') {
                    if ($targetGrade != $sourceGrade) {
                        throw ValidationException::withMessages([
                            "students.$index.target_section_id" =>
                                'Siswa yang tinggal kelas harus tetap berada pada tingkat kelas yang sama.',
                        ]);
                    }
                }
            }
        }

        $promotion = DB::transaction(function () use ($validated) {
            $promotion = Promotion::create([
                'source_academic_year_id' => $validated['source_academic_year_id'],
                'target_academic_year_id' => $validated['target_academic_year_id'],
                'source_section_id' => $validated['source_section_id'] ?? null,
                'employee_id' => $validated['employee_id'] ?? null,
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
            ]);
            foreach ($validated['students'] as $studentData) {
                PromotionItem::create([
                    'promotion_id' => $promotion->id,
                    'student_id' => $studentData['student_id'],
                    'source_student_section_id' => $studentData['source_student_section_id'] ?? null,
                    'target_section_id' => $studentData['target_section_id'] ?? null,
                    'action' => $studentData['action'],
                    'status' => 'pending',
                ]);
            }
            return $promotion;
        });

        return response()->json([
            'message' => 'Keputusan kenaikan siswa berhasil disimpan.',
            'data' => $promotion->load('items.student', 'items.targetSection'),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) {
        $promotion = Promotion::with([
            'sourceAcademicYear',
            'targetAcademicYear',
            'sourceSection',
            'employee',
            'items.student',
            'items.targetSection',
        ])->findOrFail($id);

        return response()->json([
            'message' => 'Detail riwayat keputusan kenaikan siswa berhasil diambil.',
            'data' => $promotion,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id) {
        $promotion = Promotion::with('items')->findOrFail($id);

        if ($promotion->status !== 'draft') {
            throw ValidationException::withMessages([
                'status' => 'Keputusan hanya dapat diubah jika status promosi masih draft.',
            ]);
        }

        $validated = $request->validate([
            'employee_id' => 'nullable|integer|exists:employees,id',
            'notes' => 'nullable|string',
            'students' => 'required|array|min:1',
            'students.*.student_id' => 'required|integer|exists:students,id',
            'students.*.source_student_section_id' => 'nullable|integer|exists:student_sections,id',
            'students.*.action' => 'required|in:naik,tinggal kelas,lulus,mutasi keluar,mutasi masuk',
            'students.*.target_section_id' => 'nullable|integer|exists:sections,id',
        ]);

        foreach ($validated['students'] as $index => $studentData) {
            $action = $studentData['action'];
            $targetSectionId = $studentData['target_section_id'] ?? null;

            if (in_array($action, ['naik', 'tinggal kelas', 'mutasi masuk']) && !$targetSectionId) {
                throw ValidationException::withMessages([
                    "students.$index.target_section_id" =>
                        'Kelas tujuan wajib diisi untuk keputusan ini.',
                ]);
            }

            if (in_array($action, ['lulus', 'mutasi keluar']) && $targetSectionId) {
                throw ValidationException::withMessages([
                    "students.$index.target_section_id" =>
                        'Kelas tujuan tidak diperlukan untuk keputusan ini.',
                ]);
            }

            $sourceStudentSection = null;

            if ($action !== 'mutasi masuk') {
                $sourceStudentSection = StudentSection::with('section')
                    ->where('id', $studentData['source_student_section_id'] ?? null)
                    ->where('student_id', $studentData['student_id'])
                    ->where('status', 'aktif')
                    ->first();

                if (
                    !$sourceStudentSection ||
                    !$sourceStudentSection->section ||
                    $sourceStudentSection->section->academic_year_id != $promotion->source_academic_year_id
                ) {
                    throw ValidationException::withMessages([
                        "students.$index.student_id" =>
                            'Data siswa tidak sesuai dengan tahun ajaran asal.',
                    ]);
                }

                if (
                    $promotion->source_section_id &&
                    $sourceStudentSection->section_id != $promotion->source_section_id
                ) {
                    throw ValidationException::withMessages([
                        "students.$index.student_id" =>
                            'Siswa tidak terdaftar di kelas asal yang dipilih.',
                    ]);
                }
            }

            if (
                $action === 'lulus' &&
                $sourceStudentSection &&
                $sourceStudentSection->section->grade != 6
            ) {
                throw ValidationException::withMessages([
                    "students.$index.action" =>
                        'Siswa hanya dapat dinyatakan lulus jika berada di kelas 6.',
                ]);
            }

            if ($targetSectionId) {
                $targetSection = Section::findOrFail($targetSectionId);

                if ($targetSection->academic_year_id != $promotion->target_academic_year_id) {
                    throw ValidationException::withMessages([
                        "students.$index.target_section_id" =>
                            'Kelas tujuan harus berasal dari tahun ajaran tujuan.',
                    ]);
                }

                if ($sourceStudentSection) {
                    $sourceGrade = $sourceStudentSection->section->grade;
                    $targetGrade = $targetSection->grade;

                    if ($action === 'naik') {
                        if ($sourceGrade >= 6 || $targetGrade != $sourceGrade + 1) {
                            throw ValidationException::withMessages([
                                "students.$index.target_section_id" =>
                                    'Siswa hanya dapat naik ke tingkat kelas berikutnya.',
                            ]);
                        }
                    }

                    if ($action === 'tinggal kelas') {
                        if ($targetGrade != $sourceGrade) {
                            throw ValidationException::withMessages([
                                "students.$index.target_section_id" =>
                                    'Siswa yang tinggal kelas harus tetap berada pada tingkat kelas yang sama.',
                            ]);
                        }
                    }
                }
            }
        }

        DB::transaction(function () use ($promotion, $validated) {
            $promotion->update([
                'employee_id' => $validated['employee_id'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            $promotion->items()->delete();

            foreach ($validated['students'] as $studentData) {
                $promotion->items()->create([
                    'student_id' => $studentData['student_id'],
                    'source_student_section_id' => $studentData['source_student_section_id'] ?? null,
                    'target_section_id' => $studentData['target_section_id'] ?? null,
                    'action' => $studentData['action'],
                    'status' => 'pending',
                ]);
            }
        });

        return response()->json([
            'message' => 'Keputusan kenaikan siswa berhasil diperbarui.',
            'data' => $promotion->fresh()->load('items.student', 'items.targetSection'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id) {
        $promotion = Promotion::with('items')->findOrFail($id);

        if ($promotion->status !== 'draft') {
            throw ValidationException::withMessages([
                'status' => 'Keputusan hanya dapat dihapus jika status promosi masih draft.',
            ]);
        }

        DB::transaction(function () use ($promotion) {
            $promotion->items()->delete();

            $promotion->delete();
        });

        return response()->json([
            'message' => 'Keputusan kenaikan siswa berhasil dihapus.',
            'data' => null,
        ]);
    }

    public function preview(Request $request) {
        $validated = $request->validate([
            'source_academic_year_id' => 'required|integer|exists:academic_years,id',
            'target_academic_year_id' => 'required|integer|different:source_academic_year_id|exists:academic_years,id',
            'source_section_id' => 'nullable|integer|exists:sections,id',
        ]);

        $sourceYear = AcademicYear::findOrFail(
            $validated['source_academic_year_id']
        );
        $targetYear = AcademicYear::findOrFail(
            $validated['target_academic_year_id']
        );

        $studentSections = StudentSection::with([
            'student',
            'section'
        ])
            ->where('status', 'aktif')
            ->whereHas('student')
            ->whereHas('section', function ($query) use ($sourceYear) {
                $query->where(
                    'academic_year_id',
                    $sourceYear->id
                );
            })
            ->when(
                $validated['source_section_id'] ?? null,
                function ($query, $sectionId) {
                    $query->where('section_id', $sectionId);
                }
            )
            ->get();

        $students = $studentSections->map(function ($item) {
            return [
                'student_id' => $item->student->id,
                'nama_lengkap' => $item->student->nama_lengkap,
                'nis' => $item->student->nis,

                'source_student_section_id' => $item->id,

                'source_section' => [
                    'id' => $item->section->id,
                    'grade' => $item->section->grade,
                    'name' => $item->section->name,
                ],

                'action' => null,
                'target_section_id' => null,
                'status' => 'pending',
            ];
        });

        return response()->json([
            'message' => 'Success',
            'data' => [
                'source_academic_year' => $sourceYear,
                'target_academic_year' => $targetYear,
                'students' => $students,
                'total_students' => $students->count(),
            ],
        ]);
    }

    public function undo(Request $request, string $id)
    {
        return response()->json(['message' => 'Success', 'data' => null]);
    }
}
