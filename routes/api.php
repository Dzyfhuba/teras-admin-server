<?php

use App\Http\Controllers\AcademicYearController;
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

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/students/import', [StudentsController::class, 'import']);
Route::get('/students/import/template', [StudentsImportTemplateController::class, 'download']);
Route::get('/students/{student}/sections', [StudentsController::class, 'sections']);
Route::post('/students/{student}/sections', [StudentsController::class, 'attachSection']);
Route::apiResource('students', StudentsController::class);

Route::apiResource('academic-years', AcademicYearController::class);
Route::post('/academic-years/{academic_year}/activate', [AcademicYearController::class, 'activate']);

Route::apiResource('sections', SectionController::class);
Route::get('/sections/{section}/students', [SectionController::class, 'students']);
Route::post('/sections/{section}/students', [SectionController::class, 'attachStudents']);
Route::delete('/sections/{section}/students/{student}', [SectionController::class, 'detachStudent']);

Route::apiResource('student-sections', StudentSectionController::class);
Route::patch('/student-sections/{student_section}/status', [StudentSectionController::class, 'updateStatus']);

Route::get('/employees/available-for-homeroom', [EmployeeController::class, 'availableForHomeroom']);
Route::apiResource('employees', EmployeeController::class);

Route::apiResource('school-bells', SchoolBellController::class);
Route::post('/school-bells/{school_bell}/test', [SchoolBellController::class, 'test']);

Route::apiResource('daily-notes', DailyNoteController::class);

Route::apiResource('promotions', PromotionController::class);
Route::post('/promotions/preview', [PromotionController::class, 'preview']);
Route::post('/promotions/{promotion}/undo', [PromotionController::class, 'undo']);
