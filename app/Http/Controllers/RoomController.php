<?php

namespace App\Http\Controllers;

use App\Http\Requests\Room\CreateRoomRequest;
use App\Http\Resources\RoomResource;
use App\Services\RoomService;

class RoomController extends Controller
{
    public function __construct(
        protected RoomService $roomService,
    ) {}

    public function create(CreateRoomRequest $request)
    {
        $room = $this->roomService->create($request->validated());
        return $this->successResponse(
            new RoomResource($room),
            'Room Successfully Created',
            201
        );
    }
}
