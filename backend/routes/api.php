<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\GradeController;
use App\Http\Controllers\Api\LessonAttendanceController;
use App\Http\Controllers\Api\ParentController;
use App\Http\Controllers\Api\ReportCardController;
use App\Http\Controllers\Api\SchoolProfileController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\SubjectController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\TeacherController;
use App\Http\Controllers\Api\TeacherSubjectController;
use App\Http\Controllers\Api\ClassroomController;
use App\Http\Controllers\Api\AcademicYearController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('dashboard/admin', [DashboardController::class, 'admin']);
    Route::get('dashboard/guru', [DashboardController::class, 'guru']);
    Route::get('school-profile', [SchoolProfileController::class, 'show']);
    Route::get('classes', [ClassroomController::class, 'index']);
    Route::get('teachers', [TeacherController::class, 'index']);
    Route::get('students', [StudentController::class, 'index']);
    Route::get('academic-years', [AcademicYearController::class, 'index']);
    Route::get('schedules', [ScheduleController::class, 'index']);

    Route::middleware('role:admin')->group(function (): void {
        Route::put('school-profile', [SchoolProfileController::class, 'update']);
        Route::apiResource('students', StudentController::class)->except(['index']);
        Route::apiResource('parents', ParentController::class);
        Route::apiResource('teachers', TeacherController::class)->except(['index']);
        Route::apiResource('teacher-subjects', TeacherSubjectController::class);
        Route::apiResource('classes', ClassroomController::class)->except(['index']);
        Route::apiResource('academic-years', AcademicYearController::class)->except(['index']);
        Route::apiResource('subjects', SubjectController::class);
        Route::apiResource('schedules', ScheduleController::class)->except(['index']);
    });

    Route::get('attendances', [AttendanceController::class, 'index']);
    Route::post('attendances/bulk', [AttendanceController::class, 'bulkStore']);
    Route::get('attendances/recap', [AttendanceController::class, 'recap']);
    Route::get('lesson-attendances/schedules', [LessonAttendanceController::class, 'schedules']);
    Route::post('lesson-attendances/open', [LessonAttendanceController::class, 'open']);
    Route::get('lesson-attendances/meetings/{lessonMeeting}', [LessonAttendanceController::class, 'show']);
    Route::get('lesson-attendances/meetings/{lessonMeeting}/sheet', [LessonAttendanceController::class, 'sheet']);
    Route::post('lesson-attendances/meetings/{lessonMeeting}/save', [LessonAttendanceController::class, 'save']);
    Route::get('grades/options', [GradeController::class, 'options']);

    Route::apiResource('grades', GradeController::class)->only(['index', 'store', 'update', 'show']);
    Route::get('grades/recap', [GradeController::class, 'recap']);

    Route::get('report-cards/{student}', [ReportCardController::class, 'show']);
    Route::get('report-cards/{student}/pdf', [ReportCardController::class, 'pdf']);
});
