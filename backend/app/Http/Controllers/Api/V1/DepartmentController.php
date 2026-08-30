<?php

declare(strict_types=1);
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDepartmentRequest;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
class DepartmentController extends Controller {
 public function index(Request $request): JsonResponse { $items=Department::query()->with('institution:id,name,code')->when($request->string('search')->isNotEmpty(),function($q)use($request){$s=$request->string('search')->toString();$q->where(fn($x)=>$x->where('name','ilike',"%{$s}%")->orWhere('code','ilike',"%{$s}%"));})->orderBy('name')->paginate($request->integer('per_page',15)); return response()->json(['data'=>$items]); }
 public function store(StoreDepartmentRequest $request): JsonResponse { $item=Department::create($request->validated()); return response()->json(['data'=>$item->load('institution')],201); }
 public function show(Department $department): JsonResponse { return response()->json(['data'=>$department->load('institution','courses','sections')]); }
 public function update(StoreDepartmentRequest $request, Department $department): JsonResponse { $department->update($request->validated()); return response()->json(['data'=>$department->fresh()->load('institution')]); }
 public function destroy(Department $department): JsonResponse { $department->delete(); return response()->json(null,204); }
}
