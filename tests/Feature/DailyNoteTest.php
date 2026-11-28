<?php

use App\Models\AcademicYear;
use App\Models\DailyNote;
use App\Models\Employee;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createAcademicYear(array $attributes = []): AcademicYear
{
    return AcademicYear::query()->create(array_merge([
        'name' => '2026/2027',
        'date_start' => '2026-07-01',
        'date_end' => '2027-06-30',
        'is_active' => true,
    ], $attributes));
}

function createEmployee(array $attributes = []): Employee
{
    return Employee::query()->create(array_merge([
        'nama_lengkap' => fake()->name(),
        'category' => 'guru',
        'jabatan' => 'Guru Kelas',
        'is_active' => true,
    ], $attributes));
}

function createStudent(array $attributes = []): Student
{
    return Student::query()->create(array_merge([
        'nama_lengkap' => fake()->name(),
        'nis' => fake()->unique()->numerify('2026####'),
    ], $attributes));
}

function createSection(AcademicYear $academicYear, ?Employee $waliKelas = null, array $attributes = []): Section
{
    return Section::query()->create(array_merge([
        'academic_year_id' => $academicYear->id,
        'grade' => 1,
        'name' => 'Al Fajr',
        'wali_kelas_id' => $waliKelas?->id,
    ], $attributes));
}

it('lists daily notes with pagination and filters', function (): void {
    $academicYearA = createAcademicYear(['name' => '2025/2026', 'is_active' => false]);
    $academicYearB = createAcademicYear(['name' => '2026/2027', 'is_active' => true]);
    $teacherA = createEmployee(['nama_lengkap' => 'Ustadz Ahmad']);
    $teacherB = createEmployee(['nama_lengkap' => 'Ustadzah Fatimah']);
    $studentA = createStudent(['nama_lengkap' => 'Budi Santoso']);
    $studentB = createStudent(['nama_lengkap' => 'Siti Aminah']);
    $sectionA = createSection($academicYearA, $teacherA, ['grade' => 1, 'name' => 'Al Fajr']);
    $sectionB = createSection($academicYearB, $teacherB, ['grade' => 2, 'name' => 'An Nur']);

    $targetNote = DailyNote::factory()->create([
        'student_id' => $studentA->id,
        'section_id' => $sectionA->id,
        'employee_id' => $teacherA->id,
        'academic_year_id' => $academicYearA->id,
        'date' => '2026-11-20',
        'title' => 'Perkembangan Hafalan',
        'content' => 'Siswa lancar membaca Juz 30.',
        'category' => 'akademik',
        'status' => 'selesai',
    ]);

    DailyNote::factory()->create([
        'student_id' => $studentB->id,
        'section_id' => $sectionB->id,
        'employee_id' => $teacherB->id,
        'academic_year_id' => $academicYearB->id,
        'date' => '2026-11-25',
        'title' => 'Catatan Kehadiran',
        'content' => 'Izin sakit satu hari.',
        'category' => 'kehadiran',
        'status' => 'terbuka',
    ]);

    $response = $this->getJson('/api/daily-notes?'.http_build_query([
        'date' => '2026-11-20',
        'student_id' => $studentA->id,
        'section_id' => $sectionA->id,
        'employee_id' => $teacherA->id,
        'academic_year_id' => $academicYearA->id,
        'category' => 'akademik',
        'status' => 'selesai',
        'search' => 'Hafalan',
    ]));

    $response->assertSuccessful()
        ->assertJsonPath('message', 'Data berhasil diambil')
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $targetNote->id)
        ->assertJsonPath('data.0.student.nama_lengkap', 'Budi Santoso')
        ->assertJsonPath('data.0.section.name', 'Al Fajr')
        ->assertJsonPath('data.0.employee.nama_lengkap', 'Ustadz Ahmad')
        ->assertJsonPath('data.0.academic_year.name', '2025/2026')
        ->assertJsonPath('meta.total', 1);
});

it('creates a daily note for a student and records the authenticated employee author', function (): void {
    $academicYear = createAcademicYear(['is_active' => true]);
    $user = User::factory()->create();
    $employee = createEmployee(['user_id' => $user->id, 'nama_lengkap' => 'Guru Pencatat']);
    $student = createStudent(['nama_lengkap' => 'Aisyah']);

    $response = $this->actingAs($user)->postJson('/api/daily-notes', [
        'student_id' => $student->id,
        'date' => '2026-11-28',
        'title' => 'Catatan Harian Siswa',
        'content' => 'Aktif berdiskusi di kelas.',
        'category' => 'perilaku',
        'status' => 'terbuka',
    ]);

    $response->assertCreated()
        ->assertJsonPath('message', 'Data berhasil disimpan')
        ->assertJsonPath('data.student_id', $student->id)
        ->assertJsonPath('data.employee_id', $employee->id)
        ->assertJsonPath('data.academic_year_id', $academicYear->id)
        ->assertJsonPath('data.date', '2026-11-28');

    $this->assertDatabaseHas('daily_notes', [
        'student_id' => $student->id,
        'employee_id' => $employee->id,
        'academic_year_id' => $academicYear->id,
        'title' => 'Catatan Harian Siswa',
    ]);
});

it('creates a daily note linked to a section and infers its academic year', function (): void {
    $academicYear = createAcademicYear(['name' => '2027/2028', 'is_active' => false]);
    $section = createSection($academicYear, null, ['grade' => 3, 'name' => 'An Naml']);
    $employee = createEmployee();

    $response = $this->postJson('/api/daily-notes', [
        'section_id' => $section->id,
        'employee_id' => $employee->id,
        'date' => '2026-11-28',
        'content' => 'Kegiatan praktikum berjalan tertib.',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.section_id', $section->id)
        ->assertJsonPath('data.academic_year_id', $academicYear->id);
});

it('validates required fields and requires student_id or section_id when creating', function (): void {
    $response = $this->postJson('/api/daily-notes', [
        'title' => 'Tanpa Siswa dan Rombel',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['student_id', 'section_id', 'date', 'content']);
});

it('shows, updates, and soft deletes a daily note', function (): void {
    $academicYear = createAcademicYear();
    $student = createStudent();
    $note = DailyNote::factory()->create([
        'student_id' => $student->id,
        'academic_year_id' => $academicYear->id,
        'date' => '2026-11-28',
        'title' => 'Judul Awal',
        'content' => 'Isi awal.',
        'status' => 'terbuka',
    ]);

    $this->getJson("/api/daily-notes/{$note->id}")
        ->assertSuccessful()
        ->assertJsonPath('data.id', $note->id)
        ->assertJsonPath('data.title', 'Judul Awal');

    $this->patchJson("/api/daily-notes/{$note->id}", [
        'title' => 'Judul Diperbarui',
        'status' => 'selesai',
    ])
        ->assertSuccessful()
        ->assertJsonPath('message', 'Data berhasil diperbarui')
        ->assertJsonPath('data.title', 'Judul Diperbarui')
        ->assertJsonPath('data.status', 'selesai');

    $this->patchJson("/api/daily-notes/{$note->id}", [
        'student_id' => null,
        'section_id' => null,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['student_id']);

    $this->deleteJson("/api/daily-notes/{$note->id}")
        ->assertSuccessful()
        ->assertJsonPath('message', 'Data berhasil dihapus');

    $this->assertSoftDeleted('daily_notes', ['id' => $note->id]);

    $this->getJson('/api/daily-notes')
        ->assertSuccessful()
        ->assertJsonCount(0, 'data');
});
