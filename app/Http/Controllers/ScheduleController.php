<?php

namespace App\Http\Controllers;

use App\Http\Requests\Schedule\CreateScheduleRequest;
use App\Http\Resources\Schedule\ScheduleStudentListResource;
use App\Http\Resources\Schedule\UserCourseScheduleResource;
use App\Services\ScheduleService;
use Illuminate\Http\JsonResponse;

class ScheduleController extends Controller
{
    public function __construct(
        protected ScheduleService $scheduleService
    ) {}

    public function create(CreateScheduleRequest $request)
    {
        $this->scheduleService->createSchedule($request->validated());
        return response()->json(['message' => 'Schedule successfully created.',], 201);
    }

    public function getUserCourseSchedule(string $userId)
    {
        $schedules = $this->scheduleService->getUserScheduleByActiveSemester($userId);
        return UserCourseScheduleResource::collection($schedules)
            ->message('User course schedule successfully retrieved.')
            ->status(200);
    }

    public function getScheduleStudentList(int $schedule_id)
    {
        $result = $this->scheduleService->getScheduleStudentList($schedule_id);

        return (new ScheduleStudentListResource($result))
            ->message('Schedule student list successfully retrieved.')
            ->status(200);
    }
}
