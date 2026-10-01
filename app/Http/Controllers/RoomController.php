<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Room\CreateRoomRequest;
use App\Http\Requests\Room\StoreRoomRequest;
use App\Http\Requests\Room\UpdateRoomRequest;
use App\Http\Resources\RoomResource;
use App\Services\RoomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function __construct(
        protected RoomService $roomService,
    ) {}

    /**
     * Display a listing of active rooms with optional filters.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['building_id', 'floor_no', 'status']);
        $rooms = $this->roomService->getAllRooms($filters);

        return $this->successResponse(
            RoomResource::collection($rooms),
            'Rooms retrieved successfully.',
            200
        );
    }

    /**
     * Store a newly created room.
     */
    public function store(StoreRoomRequest $request): JsonResponse
    {
        $room = $this->roomService->createRoom($request->validated());

        return $this->successResponse(
            new RoomResource($room),
            'Room created successfully.',
            201
        );
    }

    /**
     * Display the specified room with its assigned class schedules.
     */
    public function show(Request $request, int $room_id): JsonResponse
    {
        $semesterId = $request->query('semester_id') !== null ? (int) $request->query('semester_id') : null;
        $room = $this->roomService->getRoomById($room_id, true, $semesterId);

        if (! $room) {
            return $this->errorResponse('Room not found.', 404);
        }

        return $this->successResponse(
            new RoomResource($room),
            'Room retrieved successfully.',
            200
        );
    }

    /**
     * Update the specified room.
     */
    public function update(UpdateRoomRequest $request, int $room_id): JsonResponse
    {
        $room = $this->roomService->updateRoom($room_id, $request->validated());

        return $this->successResponse(
            new RoomResource($room),
            'Room updated successfully.',
            200
        );
    }

    /**
     * Archive (soft-delete) the specified room.
     */
    public function destroy(int $room_id): JsonResponse
    {
        $this->roomService->deleteRoom($room_id);

        return $this->successResponse(
            null,
            'Room archived successfully.',
            200
        );
    }

    /**
     * Display a listing of archived rooms.
     */
    public function archives(): JsonResponse
    {
        $archived = $this->roomService->getArchivedRooms();

        return $this->successResponse(
            RoomResource::collection($archived),
            'Archived rooms retrieved successfully.',
            200
        );
    }

    /**
     * Restore an archived room.
     */
    public function restore(int $room_id): JsonResponse
    {
        $restored = $this->roomService->restoreRoom($room_id);

        return $this->successResponse(
            new RoomResource($restored),
            'Room restored successfully.',
            200
        );
    }

    /**
     * Legacy room creation endpoint.
     */
    public function create(CreateRoomRequest $request): JsonResponse
    {
        $room = $this->roomService->create($request->validated());

        return $this->successResponse(
            new RoomResource($room),
            'Room Successfully Created',
            201
        );
    }
}
