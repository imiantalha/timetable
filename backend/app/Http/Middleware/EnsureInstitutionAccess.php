<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Course;
use App\Models\Department;
use App\Models\Room;
use App\Models\Section;
use App\Models\Teacher;
use App\Models\TimeSlot;
use App\Models\Timetable;
use App\Models\TimetableEntry;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureInstitutionAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        abort_unless($user, 401);

        if ($user->institution_id === null) {
            abort_unless($user->role === 'admin', 403);
            return $next($request);
        }

        $institutionId = (int) $user->institution_id;

        foreach ($request->route()->parameters() as $resource) {
            $resourceInstitutionId = match (true) {
                $resource instanceof Department,
                $resource instanceof Room,
                $resource instanceof Teacher,
                $resource instanceof TimeSlot,
                $resource instanceof Timetable => (int) $resource->institution_id,
                $resource instanceof Course => (int) $resource->department()->value('institution_id'),
                $resource instanceof Section => (int) $resource->department()->value('institution_id'),
                $resource instanceof TimetableEntry => (int) $resource->timetable()->value('institution_id'),
                default => null,
            };

            if ($resourceInstitutionId !== null) {
                abort_unless($resourceInstitutionId === $institutionId, 404);
            }
        }

        return $next($request);
    }
}
