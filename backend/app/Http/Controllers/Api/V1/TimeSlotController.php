<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTimeSlotRequest;
use App\Models\TimeSlot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimeSlotController extends Controller
{
    public function index(Request $request): JsonResponse { $items=TimeSlot::query()->orderBy('day_of_week')->orderBy('starts_at')->paginate($request->integer('per_page',30)); return response()->json(['data'=>$items]); }
    public function store(StoreTimeSlotRequest $request): JsonResponse { return response()->json(['data'=>TimeSlot::create($request->validated())],201); }
    public function show(TimeSlot $timeSlot): JsonResponse { return response()->json(['data'=>$timeSlot->load('institution')]); }
    public function update(StoreTimeSlotRequest $request, TimeSlot $timeSlot): JsonResponse { $timeSlot->update($request->validated()); return response()->json(['data'=>$timeSlot->fresh()]); }
    public function destroy(TimeSlot $timeSlot): JsonResponse { $timeSlot->delete(); return response()->json(null,204); }
}
