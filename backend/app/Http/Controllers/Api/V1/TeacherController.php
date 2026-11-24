<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeacherRequest;
use App\Models\Department;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function index(Request $request): JsonResponse { $items=Teacher::query()->when($request->user()?->institution_id, fn ($q, $id) => $q->where('institution_id', $id))->when($request->filled('department_id'), function ($q) use ($request): void { $institutionId = Department::whereKey($request->integer('department_id'))->value('institution_id'); if ($institutionId !== null) $q->where('institution_id', $institutionId); })->when($request->string('search')->isNotEmpty(),fn($q)=>$q->where(fn($x)=>$x->where('first_name','ilike','%'.$request->string('search').'%')->orWhere('last_name','ilike','%'.$request->string('search').'%')->orWhere('employee_number','ilike','%'.$request->string('search').'%')))->orderBy('last_name')->paginate(min(max($request->integer('per_page', 15), 1), 100)); return response()->json(['data'=>$items]); }
    public function store(StoreTeacherRequest $request): JsonResponse { return response()->json(['data'=>Teacher::create($request->validated())],201); }
    public function show(Teacher $teacher): JsonResponse { return response()->json(['data'=>$teacher->load('institution','availabilities')]); }
    public function update(StoreTeacherRequest $request, Teacher $teacher): JsonResponse { $teacher->update($request->validated()); return response()->json(['data'=>$teacher->fresh()]); }
    public function destroy(Teacher $teacher): JsonResponse { abort_unless(request()->user()?->role === 'admin', 403); $teacher->delete(); return response()->json(null,204); }
}
