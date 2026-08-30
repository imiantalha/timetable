<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Scheduling\ConflictDetector;
use App\Http\Controllers\Controller;
use App\Models\TimetableEntry;
use Illuminate\Http\JsonResponse;

final class ConflictController extends Controller
{
    public function __construct(private readonly ConflictDetector $detector) {}

    public function check(TimetableEntry $timetableEntry): JsonResponse
    {
        $conflicts = $this->detector->detect($timetableEntry);

        return response()->json([
            'data' => [
                'valid' => $conflicts->isEmpty(),
                'conflicts' => $conflicts->values(),
            ],
        ]);
    }
}
