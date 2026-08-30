<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\CourseController;
use App\Http\Controllers\Api\V1\DepartmentController;
use App\Http\Controllers\Api\V1\RoomController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', static fn () => response()->json(['status' => 'ok', 'service' => 'timetable-api']));

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::apiResource('courses', CourseController::class);
        Route::apiResource('departments', DepartmentController::class);
        Route::apiResource('rooms', RoomController::class);
    });
});
