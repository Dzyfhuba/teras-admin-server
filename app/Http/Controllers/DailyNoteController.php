<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDailyNoteRequest;
use App\Http\Requests\UpdateDailyNoteRequest;
use App\Http\Resources\DailyNoteResource;
use App\Models\AcademicYear;
use App\Models\DailyNote;
use App\Models\Employee;
use App\Models\Section;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DailyNoteController extends Controller
{
    /**
     * Display a listing of daily notes with optional filters and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = DailyNote::query()->with(['student', 'section', 'employee', 'academicYear']);

        if ($request->boolean('with_trashed')) {
            $query->withTrashed();
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->string('date')->toString());
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->string('date_from')->toString());
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->string('date_to')->toString());
        }

        foreach (['student_id', 'section_id', 'employee_id', 'academic_year_id', 'category', 'status'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($subQuery) use ($search): void {
                $subQuery->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $allowedSorts = ['date', 'created_at', 'updated_at', 'title', 'category', 'status'];
        $sort = $request->string('sort')->toString();
        $sortColumn = in_array($sort, $allowedSorts, true) ? $sort : 'date';
        $direction = strtolower($request->string('direction')->toString()) === 'asc' ? 'asc' : 'desc';

        $perPage = max(1, min(100, $request->integer('per_page', 15)));

        $notes = $query
            ->orderBy($sortColumn, $direction)
            ->orderByDesc('id')
            ->paginate($perPage);

        return response()->json([
            'message' => 'Data berhasil diambil',
            'data' => DailyNoteResource::collection($notes->items())->resolve($request),
            'meta' => [
                'current_page' => $notes->currentPage(),
                'last_page' => $notes->lastPage(),
                'per_page' => $notes->perPage(),
                'total' => $notes->total(),
            ],
        ]);
    }

    /**
     * Store a newly created daily note in storage.
     */
    public function store(StoreDailyNoteRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (empty($data['employee_id']) && $request->user() !== null) {
            $data['employee_id'] = Employee::query()
                ->where('user_id', $request->user()->id)
                ->value('id');
        }

        if (empty($data['academic_year_id'])) {
            $data['academic_year_id'] = $this->resolveAcademicYearId($data['section_id'] ?? null);
        }

        $dailyNote = DailyNote::query()->create($data);
        $dailyNote->load(['student', 'section', 'employee', 'academicYear']);

        return response()->json([
            'message' => 'Data berhasil disimpan',
            'data' => new DailyNoteResource($dailyNote),
        ], 201);
    }

    /**
     * Display the specified daily note.
     */
    public function show(DailyNote $dailyNote): JsonResponse
    {
        $dailyNote->load(['student', 'section', 'employee', 'academicYear']);

        return response()->json([
            'message' => 'Data berhasil diambil',
            'data' => new DailyNoteResource($dailyNote),
        ]);
    }

    /**
     * Update the specified daily note in storage.
     */
    public function update(UpdateDailyNoteRequest $request, DailyNote $dailyNote): JsonResponse
    {
        $dailyNote->update($request->validated());
        $dailyNote->refresh();
        $dailyNote->load(['student', 'section', 'employee', 'academicYear']);

        return response()->json([
            'message' => 'Data berhasil diperbarui',
            'data' => new DailyNoteResource($dailyNote),
        ]);
    }

    /**
     * Soft delete the specified daily note from storage.
     */
    public function destroy(DailyNote $dailyNote): JsonResponse
    {
        $dailyNote->delete();

        return response()->json([
            'message' => 'Data berhasil dihapus',
            'data' => null,
        ]);
    }

    /**
     * Resolve academic year ID from the given section or the active academic year.
     */
    private function resolveAcademicYearId(?int $sectionId): ?int
    {
        if ($sectionId !== null) {
            $sectionAcademicYearId = Section::query()
                ->whereKey($sectionId)
                ->value('academic_year_id');

            if ($sectionAcademicYearId !== null) {
                return (int) $sectionAcademicYearId;
            }
        }

        $activeAcademicYearId = AcademicYear::query()
            ->where('is_active', true)
            ->value('id');

        return $activeAcademicYearId !== null ? (int) $activeAcademicYearId : null;
    }
}
