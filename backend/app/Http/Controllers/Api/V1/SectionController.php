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
    public function index(Request $request): JsonResponse
    {
        $items = Section::query()
            ->when($request->user()?->institution_id, fn ($query, $id) => $query->whereHas('department', fn ($department) => $department->where('institution_id', $id)))
            ->with('department:id,name,code')
            ->when($request->filled('department_id'), fn ($query) => $query->where('department_id', $request->integer('department_id')))
            ->when($request->string('search')->isNotEmpty(), function ($query) use ($request): void {
                $search = $request->string('search')->toString();
                $query->where(fn ($builder) => $builder->where('name', 'ilike', "%{$search}%")
                    ->orWhere('code', 'ilike', "%{$search}%"));
            })
            ->orderBy('name')
            ->paginate($request->integer('per_page', 15));

        return response()->json(['data' => $items]);
    }

    public function store(StoreSectionRequest $request): JsonResponse
    {
        return response()->json(['data' => Section::create($request->validated())->load('department')], 201);
    }

    public function show(Section $section): JsonResponse
    {
        return response()->json(['data' => $section->load('department', 'students')]);
    }

    public function update(StoreSectionRequest $request, Section $section): JsonResponse
    {
        $section->update($request->validated());
        return response()->json(['data' => $section->fresh()->load('department')]);
    }

    public function destroy(Section $section): JsonResponse
    {
        abort_unless(request()->user()?->role === 'admin', 403);
        $section->delete();
        return response()->json(null, 204);
    }
}
