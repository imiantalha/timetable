<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseRequest;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $courses = Course::query()
            ->with('department:id,name,code')
            ->when($request->string('search')->isNotEmpty(), function ($query) use ($request): void {
                $search = $request->string('search')->toString();
                $query->where(fn ($q) => $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('code', 'ilike', "%{$search}%"));
            })
            ->orderBy('name')
            ->paginate($request->integer('per_page', 15));

        return response()->json(['data' => $courses]);
    }

    public function store(StoreCourseRequest $request): JsonResponse
    {
        $course = Course::create($request->validated());
        return response()->json(['data' => $course->load('department')], 201);
    }

    public function show(Course $course): JsonResponse
    {
        return response()->json(['data' => $course->load('department')]);
    }

    public function update(StoreCourseRequest $request, Course $course): JsonResponse
    {
        $course->update($request->validated());
        return response()->json(['data' => $course->fresh()->load('department')]);
    }

    public function destroy(Course $course): JsonResponse
    {
        $course->delete();
        return response()->json(null, 204);
    }
}
