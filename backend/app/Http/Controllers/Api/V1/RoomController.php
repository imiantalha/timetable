<?php

declare(strict_types=1);
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoomRequest;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
class RoomController extends Controller {
 public function index(Request $request): JsonResponse { $items=Room::query()->when($request->string('search')->isNotEmpty(),function($q)use($request){$s=$request->string('search')->toString();$q->where(fn($x)=>$x->where('name','ilike',"%{$s}%")->orWhere('code','ilike',"%{$s}%"));})->orderBy('name')->paginate($request->integer('per_page',15)); return response()->json(['data'=>$items]); }
 public function store(StoreRoomRequest $request): JsonResponse { return response()->json(['data'=>Room::create($request->validated())],201); }
 public function show(Room $room): JsonResponse { return response()->json(['data'=>$room->load('institution','availabilities')]); }
 public function update(StoreRoomRequest $request, Room $room): JsonResponse { $room->update($request->validated()); return response()->json(['data'=>$room->fresh()]); }
 public function destroy(Room $room): JsonResponse { $room->delete(); return response()->json(null,204); }
}
