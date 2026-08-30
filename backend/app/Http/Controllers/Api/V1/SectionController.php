<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSectionRequest;
use App\Models\Section;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function index(Request $request): JsonResponse { $items=Section::query()->with('department:id,name,code')->when($request->string('search')->isNotEmpty(),fn($q)=>$q->where(fn($x)=>$x->where('name','ilike','%'.$request->string('search').'%')->orWhere('code','ilike','%'.$request->string('search').'%')))->orderBy('name')->paginate($request->integer('per_page',15)); return response()->json(['data'=>$items]); }
    public function store(StoreSectionRequest $request): JsonResponse { return response()->json(['data'=>Section::create($request->validated())->load('department')],201); }
    public function show(Section $section): JsonResponse { return response()->json(['data'=>$section->load('department','students')]); }
    public function update(StoreSectionRequest $request, Section $section): JsonResponse { $section->update($request->validated()); return response()->json(['data'=>$section->fresh()->load('department')]); }
    public function destroy(Section $section): JsonResponse { $section->delete(); return response()->json(null,204); }
}
