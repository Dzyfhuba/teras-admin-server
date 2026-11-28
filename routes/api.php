<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DailyNoteController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\SchoolBellController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\StudentsController;
use App\Http\Controllers\StudentSectionController;
use App\Http\Controllers\StudentsImportTemplateController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::middleware('role:admin,kepala_sekolah,guru,staff')->group(function (): void {
        Route::get('/students/import/template', [StudentsImportTemplateController::class, 'download']);
        Route::get('/students/{student}/sections', [StudentsController::class, 'sections']);
        Route::apiResource('students', StudentsController::class)->only(['index', 'show']);

        Route::apiResource('academic-years', AcademicYearController::class)->only(['index', 'show']);

        Route::get('/sections/{section}/students', [SectionController::class, 'students']);
        Route::apiResource('sections', SectionController::class)->only(['index', 'show']);

        Route::apiResource('student-sections', StudentSectionController::class)->only(['index', 'show']);

        Route::get('/employees/available-for-homeroom', [EmployeeController::class, 'availableForHomeroom']);
        Route::apiResource('employees', EmployeeController::class)->only(['index', 'show']);

        Route::apiResource('school-bells', SchoolBellController::class)->only(['index', 'show']);

        Route::apiResource('daily-notes', DailyNoteController::class);

        Route::apiResource('promotions', PromotionController::class)->only(['index', 'show']);
    });

    Route::middleware('role:admin,kepala_sekolah,staff')->group(function (): void {
        Route::post('/students/import', [StudentsController::class, 'import']);
        Route::post('/students/{student}/sections', [StudentsController::class, 'attachSection']);
        Route::apiResource('students', StudentsController::class)->only(['store', 'update']);

        Route::apiResource('academic-years', AcademicYearController::class)->only(['store', 'update', 'destroy']);
        Route::post('/academic-years/{academic_year}/activate', [AcademicYearController::class, 'activate']);

        Route::apiResource('sections', SectionController::class)->only(['store', 'update', 'destroy']);
        Route::post('/sections/{section}/students', [SectionController::class, 'attachStudents']);
        Route::delete('/sections/{section}/students/{student}', [SectionController::class, 'detachStudent']);

        Route::apiResource('student-sections', StudentSectionController::class)->only(['store', 'update', 'destroy']);
        Route::patch('/student-sections/{student_section}/status', [StudentSectionController::class, 'updateStatus']);

        Route::apiResource('employees', EmployeeController::class)->only(['store', 'update', 'destroy']);

        Route::apiResource('school-bells', SchoolBellController::class)->only(['store', 'update', 'destroy']);
        Route::post('/school-bells/{school_bell}/test', [SchoolBellController::class, 'test']);
    });

    Route::middleware('role:admin,kepala_sekolah')->group(function (): void {
        Route::delete('/students/{student}', [StudentsController::class, 'destroy']);

        Route::apiResource('promotions', PromotionController::class)->only(['store', 'update', 'destroy']);
        Route::post('/promotions/preview', [PromotionController::class, 'preview']);
        Route::post('/promotions/{promotion}/undo', [PromotionController::class, 'undo']);
    });
});
