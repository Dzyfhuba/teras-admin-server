<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AcademicYearController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = AcademicYear::query();

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->toString();
            $query->where('name', 'like', "%{$search}%");
        }

        $isActive = $this->parseNullableBoolean($request->query('is_active'));
        if ($isActive !== null) {
            $query->where('is_active', $isActive);
        }

        $allowedSorts = ['id', 'name', 'date_start', 'date_end', 'is_active', 'created_at'];
        $sort = in_array($request->query('sort'), $allowedSorts, true)
            ? (string) $request->query('sort')
            : 'name';
        $direction = strtolower((string) $request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sort, $direction);
        if ($sort !== 'id') {
            $query->orderBy('id', $direction);
        }

        $perPage = max(1, min(100, $request->integer('per_page', 15)));
        $academicYears = $query->paginate($perPage);

        return response()->json([
            'message' => 'Data tahun ajaran berhasil diambil',
            'data' => collect($academicYears->items())
                ->map(fn (AcademicYear $academicYear): array => $this->formatAcademicYear($academicYear))
                ->values(),
            'meta' => [
                'current_page' => $academicYears->currentPage(),
                'last_page' => $academicYears->lastPage(),
                'per_page' => $academicYears->perPage(),
                'total' => $academicYears->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateAcademicYear($request);
        $data['is_active'] ??= false;

        $academicYear = DB::transaction(function () use ($data): AcademicYear {
            if ($data['is_active'] === true) {
                AcademicYear::query()->where('is_active', true)->update(['is_active' => false]);
            }

            return AcademicYear::create($data)->refresh();
        });

        return response()->json([
            'message' => 'Data tahun ajaran berhasil disimpan',
            'data' => $this->formatAcademicYear($academicYear),
        ], 201);
    }

    public function show(AcademicYear $academicYear): JsonResponse
    {
        return response()->json([
            'message' => 'Detail tahun ajaran berhasil diambil',
            'data' => $this->formatAcademicYear($academicYear),
        ]);
    }

    public function update(Request $request, AcademicYear $academicYear): JsonResponse
    {
        $data = $this->validateAcademicYear($request, $academicYear);

        DB::transaction(function () use ($academicYear, $data): void {
            if (($data['is_active'] ?? false) === true) {
                AcademicYear::query()
                    ->where('id', '!=', $academicYear->id)
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
            }

            $academicYear->update($data);
        });

        return response()->json([
            'message' => 'Data tahun ajaran berhasil diperbarui',
            'data' => $this->formatAcademicYear($academicYear->refresh()),
        ]);
    }

    public function destroy(AcademicYear $academicYear): JsonResponse
    {
        DB::transaction(function () use ($academicYear): void {
            if ($academicYear->is_active) {
                $academicYear->update(['is_active' => false]);
            }

            $academicYear->delete();
        });

        return response()->json([
            'message' => 'Data tahun ajaran berhasil dihapus',
            'data' => null,
        ]);
    }

    public function activate(AcademicYear $academicYear): JsonResponse
    {
        DB::transaction(function () use ($academicYear): void {
            AcademicYear::query()
                ->where('id', '!=', $academicYear->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            if (! $academicYear->is_active) {
                $academicYear->update(['is_active' => true]);
            }
        });

        return response()->json([
            'message' => 'Tahun ajaran berhasil diaktifkan',
            'data' => $this->formatAcademicYear($academicYear->refresh()),
        ]);
    }

    /**
     * Validate request input for creating or updating an academic year.
     *
     * @return array<string, mixed>
     */
    private function validateAcademicYear(Request $request, ?AcademicYear $academicYear = null): array
    {
        $nameUniqueRule = Rule::unique('academic_years', 'name')->whereNull('deleted_at');

        if ($academicYear !== null) {
            $nameUniqueRule->ignore($academicYear->id);
        }

        $nameRules = $academicYear === null
            ? ['required', 'string', 'max:255', $nameUniqueRule]
            : ['sometimes', 'required', 'string', 'max:255', $nameUniqueRule];

        $validated = $request->validate([
            'name' => $nameRules,
            'date_start' => ['nullable', 'date'],
            'date_end' => ['nullable', 'date'],
            'is_active' => ['sometimes', 'boolean'],
        ], [
            'name.required' => 'Nama tahun ajaran wajib diisi.',
            'name.unique' => 'Nama tahun ajaran sudah terdaftar.',
            'date_start.date' => 'Format tanggal mulai tidak valid.',
            'date_end.date' => 'Format tanggal selesai tidak valid.',
            'is_active.boolean' => 'Status aktif harus berupa nilai boolean.',
        ]);

        $effectiveStart = array_key_exists('date_start', $validated)
            ? $validated['date_start']
            : $academicYear?->date_start?->toDateString();

        $effectiveEnd = array_key_exists('date_end', $validated)
            ? $validated['date_end']
            : $academicYear?->date_end?->toDateString();

        if ($effectiveStart !== null && $effectiveEnd !== null && strtotime((string) $effectiveEnd) < strtotime((string) $effectiveStart)) {
            throw ValidationException::withMessages([
                'date_end' => ['Tanggal selesai harus sama dengan atau setelah tanggal mulai.'],
            ]);
        }

        return $validated;
    }

    /**
     * Format an academic year model for JSON API responses.
     *
     * @return array<string, mixed>
     */
    private function formatAcademicYear(AcademicYear $academicYear): array
    {
        return [
            'id' => $academicYear->id,
            'name' => $academicYear->name,
            'date_start' => $academicYear->date_start?->toDateString(),
            'date_end' => $academicYear->date_end?->toDateString(),
            'is_active' => (bool) $academicYear->is_active,
            'created_at' => $academicYear->created_at?->toISOString(),
            'updated_at' => $academicYear->updated_at?->toISOString(),
        ];
    }

    private function parseNullableBoolean(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }
}