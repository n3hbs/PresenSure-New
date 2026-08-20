<?php

namespace App\Http\Controllers;

use App\Http\Requests\Schedule\CreateScheduleRequest;
use App\Http\Resources\UserCourseScheduleResource;
use App\Services\ScheduleService;
use Illuminate\Http\JsonResponse;

class ScheduleController extends Controller
{
    public function __construct(
        protected ScheduleService $scheduleService
    ) {}

    public function create(CreateScheduleRequest $request){
        $this->scheduleService->createSchedule($request->validated());
        return response()->json(['message' => 'Schedule successfully created.',], 201);
    }

    public function getUserCourseSchedule(string $userId)
    {
        $schedules = $this->scheduleService->getUserScheduleByActiveSemester($userId);
        return response()->json(['data' => UserCourseScheduleResource::collection($schedules),]);
    }

    public function getScheduleStudentList(int $schedule_id): JsonResponse
    {
        $result = $this->scheduleService->getScheduleStudentList($schedule_id);

        return $this->successResponse(
            $result['data'],
            $result['message']
        );
    }
}
