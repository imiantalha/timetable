<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Timetable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class TimetableController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = Timetable::query()->with('semester.academicYear')->orderByDesc('created_at')->paginate($request->integer('per_page', 15));
        return response()->json(['data' => $items]);
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()?->role === 'admin', 403);
        $data = $request->validate([
            'institution_id' => ['required','integer','exists:institutions,id'],
            'semester_id' => ['required','integer','exists:semesters,id'],
            'name' => ['required','string','max:255'],
            'status' => ['sometimes', Rule::in(['draft','published'])],
        ]);
        $item = Timetable::create($data);
        return response()->json(['data' => $item->load('semester.academicYear')], 201);
    }

    public function show(Timetable $timetable): JsonResponse
    {
        return response()->json(['data' => $timetable->load(['semester.academicYear','entries.course','entries.teacher','entries.section','entries.room','entries.timeSlot'])]);
    }

    public function publish(Request $request, Timetable $timetable): JsonResponse
    {
        abort_unless($request->user()?->role === 'admin', 403);
        $timetable->update(['status' => 'published', 'published_at' => now()]);
        return response()->json(['data' => $timetable->fresh()]);
    }
}
