<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Employee::query()->with('user:id,name,email');

        if ($request->filled('search')) {
            $query->search($request->string('search')->trim()->toString());
        }

        if ($request->filled('category')) {
            $query->ofCategory($request->string('category')->toString());
        }

        $isActive = $this->parseNullableBoolean($request->query('is_active'));
        if ($isActive !== null) {
            $query->where('is_active', $isActive);
        }

        if ($request->filled('jenis_kelamin')) {
            $query->where('jenis_kelamin', $request->string('jenis_kelamin')->toString());
        }

        $hasUser = $this->parseNullableBoolean($request->query('has_user'));
        if ($hasUser === true) {
            $query->whereNotNull('user_id');
        } elseif ($hasUser === false) {
            $query->whereNull('user_id');
        }

        $allowedSorts = ['id', 'nama_lengkap', 'category', 'nip', 'jabatan', 'is_active', 'created_at'];
        $sort = in_array($request->query('sort'), $allowedSorts, true)
            ? (string) $request->query('sort')
            : 'nama_lengkap';
        $direction = strtolower((string) $request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        $query->orderBy($sort, $direction);
        if ($sort !== 'id') {
            $query->orderBy('id', $direction);
        }

        $perPage = max(1, min(100, $request->integer('per_page', 15)));
        $employees = $query->paginate($perPage);

        return response()->json([
            'message' => 'Data guru berhasil diambil',
            'data' => collect($employees->items())
                ->map(fn (Employee $employee): array => $this->formatEmployee($employee))
                ->values(),
            'meta' => [
                'current_page' => $employees->currentPage(),
                'last_page' => $employees->lastPage(),
                'per_page' => $employees->perPage(),
                'total' => $employees->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateEmployee($request);
        $data['is_active'] ??= true;

        $employee = Employee::create($data)->refresh()->load('user:id,name,email');

        return response()->json([
            'message' => 'Data guru berhasil disimpan',
            'data' => $this->formatEmployee($employee),
        ], 201);
    }

    public function show(Employee $employee): JsonResponse
    {
        $employee->load('user:id,name,email');

        return response()->json([
            'message' => 'Detail guru berhasil diambil',
            'data' => $this->formatEmployee($employee),
        ]);
    }

    public function update(Request $request, Employee $employee): JsonResponse
    {
        $data = $this->validateEmployee($request, $employee);

        $employee->update($data);
        $employee->load('user:id,name,email');

        return response()->json([
            'message' => 'Data guru berhasil diperbarui',
            'data' => $this->formatEmployee($employee),
        ]);
    }

    public function destroy(Employee $employee): JsonResponse
    {
        $employee->delete();

        return response()->json([
            'message' => 'Data guru berhasil dihapus',
            'data' => null,
        ]);
    }

    public function availableForHomeroom(Request $request): JsonResponse
    {
        $academicYearId = $request->filled('academic_year_id')
            ? $request->integer('academic_year_id')
            : AcademicYear::query()->where('is_active', true)->value('id');

        $excludeSectionId = $request->filled('exclude_section_id')
            ? $request->integer('exclude_section_id')
            : null;

        $query = Employee::query()
            ->with('user:id,name,email')
            ->active()
            ->ofCategory('guru');

        if ($request->filled('search')) {
            $query->search($request->string('search')->trim()->toString());
        }

        if ($academicYearId !== null) {
            $query->whereDoesntHave('sections', function (Builder $sectionQuery) use ($academicYearId, $excludeSectionId): void {
                $sectionQuery->where('academic_year_id', $academicYearId);

                if ($excludeSectionId !== null) {
                    $sectionQuery->where('id', '!=', $excludeSectionId);
                }
            });
        }

        $employees = $query
            ->orderBy('nama_lengkap')
            ->orderBy('id')
            ->get();

        return response()->json([
            'message' => 'Data guru tersedia untuk wali kelas berhasil diambil',
            'data' => $employees
                ->map(fn (Employee $employee): array => $this->formatEmployee($employee))
                ->values(),
        ]);
    }

    /**
     * Validate request input for creating or updating an employee.
     *
     * @return array<string, mixed>
     */
    private function validateEmployee(Request $request, ?Employee $employee = null): array
    {
        $userUniqueRule = Rule::unique('employees', 'user_id')->whereNull('deleted_at');

        if ($employee !== null) {
            $userUniqueRule->ignore($employee->id);
        }

        $namaRules = $employee === null
            ? ['required', 'string', 'max:255']
            : ['sometimes', 'required', 'string', 'max:255'];

        return $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id', $userUniqueRule],
            'nama_lengkap' => $namaRules,
            'title_depan' => ['nullable', 'string', 'max:50'],
            'title_belakang' => ['nullable', 'string', 'max:50'],
            'category' => ['nullable', 'string', Rule::in(['guru', 'tendik', 'non-satmikal'])],
            'nik' => ['nullable', 'string', 'max:50'],
            'nip' => ['nullable', 'string', 'max:50'],
            'nuptk' => ['nullable', 'string', 'max:50'],
            'niy' => ['nullable', 'string', 'max:50'],
            'jenis_kelamin' => ['nullable', 'string', Rule::in(['Laki-laki', 'Perempuan'])],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'telepon' => ['nullable', 'string', 'max:30'],
            'pendidikan_terakhir' => ['nullable', 'string', 'max:50'],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'tmt_jabatan' => ['nullable', 'string', 'max:50'],
            'is_active' => ['sometimes', 'boolean'],
        ], [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'user_id.exists' => 'Akun pengguna tidak ditemukan.',
            'user_id.unique' => 'Akun pengguna sudah terhubung ke data guru lain.',
            'category.in' => 'Kategori harus bernilai guru, tendik, atau non-satmikal.',
            'jenis_kelamin.in' => 'Jenis kelamin harus bernilai Laki-laki atau Perempuan.',
            'tanggal_lahir.date' => 'Format tanggal lahir tidak valid.',
            'is_active.boolean' => 'Status aktif harus berupa nilai boolean.',
        ]);
    }

    /**
     * Format an employee model for JSON API responses.
     *
     * @return array<string, mixed>
     */
    private function formatEmployee(Employee $employee): array
    {
        $data = [
            'id' => $employee->id,
            'user_id' => $employee->user_id,
            'nama_lengkap' => $employee->nama_lengkap,
            'title_depan' => $employee->title_depan,
            'title_belakang' => $employee->title_belakang,
            'category' => $employee->category,
            'nik' => $employee->nik,
            'nip' => $employee->nip,
            'nuptk' => $employee->nuptk,
            'niy' => $employee->niy,
            'jenis_kelamin' => $employee->jenis_kelamin,
            'tempat_lahir' => $employee->tempat_lahir,
            'tanggal_lahir' => $employee->tanggal_lahir?->toDateString(),
            'alamat' => $employee->alamat,
            'telepon' => $employee->telepon,
            'pendidikan_terakhir' => $employee->pendidikan_terakhir,
            'jabatan' => $employee->jabatan,
            'tmt_jabatan' => $employee->tmt_jabatan,
            'is_active' => (bool) $employee->is_active,
            'created_at' => $employee->created_at?->toISOString(),
            'updated_at' => $employee->updated_at?->toISOString(),
        ];

        if ($employee->relationLoaded('user')) {
            $data['user'] = $employee->user === null ? null : [
                'id' => $employee->user->id,
                'name' => $employee->user->name,
                'email' => $employee->user->email,
            ];
        }

        return $data;
    }

    private function parseNullableBoolean(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }
}
