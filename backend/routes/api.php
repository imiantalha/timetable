<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\ConflictController;
use App\Http\Controllers\Api\V1\CourseController;
use App\Http\Controllers\Api\V1\DepartmentController;
use App\Http\Controllers\Api\V1\RoomController;
use App\Http\Controllers\Api\V1\SectionController;
use App\Http\Controllers\Api\V1\TeacherController;
use App\Http\Controllers\Api\V1\TimeSlotController;
use App\Http\Controllers\Api\V1\TimetableController;
use App\Http\Controllers\Api\V1\TimetableEntryController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', static fn () => response()->json(['status' => 'ok', 'service' => 'timetable-api']));

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::apiResource('courses', CourseController::class);
        Route::apiResource('departments', DepartmentController::class);
        Route::apiResource('rooms', RoomController::class);
        Route::apiResource('teachers', TeacherController::class);
        Route::apiResource('sections', SectionController::class);
        Route::apiResource('time-slots', TimeSlotController::class);
        Route::apiResource('timetables', TimetableController::class)->only(['index','store','show']);
        Route::post('timetables/{timetable}/publish', [TimetableController::class, 'publish']);
        Route::apiResource('timetable-entries', TimetableEntryController::class)->only(['index','store','show','destroy']);
        Route::get('timetable-entries/{timetableEntry}/conflicts', [ConflictController::class, 'check']);
    });
});
