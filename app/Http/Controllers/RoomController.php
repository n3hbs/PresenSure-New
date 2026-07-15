<?php

namespace App\Http\Controllers;

use App\Http\Requests\Room\CreateRoomRequest;
use App\Services\RoomService;

class RoomController extends Controller
{
    public function __construct(
        protected RoomService $roomService,
    ) {}

    public function create(CreateRoomRequest $request) {
        $this->roomService->create($request->validated());
        return response()->json(['message' => 'Room Successfully Created',], 201);
    }
}
