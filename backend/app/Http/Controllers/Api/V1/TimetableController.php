<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Timetable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

final class TimetableController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = Timetable::query()->when($request->user()?->institution_id, fn ($query, $id) => $query->where('institution_id', $id))->with('semester.academicYear')->orderByDesc('created_at')->paginate(min(max($request->integer('per_page', 15), 1), 100));

        return response()->json(['data' => $items]);
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()?->role === 'admin', 403);

        $data = $request->validate([
            'institution_id' => ['required','integer','exists:institutions,id'],
            'semester_id' => ['required','integer','exists:semesters,id'],
            'name' => ['required','string','max:255'],
            'status' => ['sometimes', Rule::in(['draft'])],
        ]);

        if ($request->user()?->institution_id !== null
            && (int) $request->user()->institution_id !== (int) $data['institution_id']) {
            abort(404);
        }

        $semester = Semester::with('academicYear')->findOrFail($data['semester_id']);

        if ((int) $semester->academicYear->institution_id !== (int) $data['institution_id']) {
            throw ValidationException::withMessages([
                'semester_id' => ['The selected semester does not belong to the selected institution.'],
            ]);
        }

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

        $published = DB::transaction(function () use ($timetable): Timetable {
            $locked = Timetable::query()->whereKey($timetable->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== 'draft') {
                throw new ConflictHttpException('Only draft timetables can be published.');
            }

            if (! $locked->entries()->exists()) {
                throw ValidationException::withMessages([
                    'timetable' => ['Add at least one valid timetable entry before publishing.'],
                ]);
            }

            $locked->update(['status' => 'published', 'published_at' => now()]);

            return $locked->fresh();
        });

        return response()->json(['data' => $published]);
    }
}
