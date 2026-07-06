<?php

namespace App\Http\Controllers;

use App\Http\Requests\Schedule\CreateScheduleRequest;
use App\Services\ScheduleService;

class ScheduleController extends Controller
{
    public function __construct(
        protected ScheduleService $scheduleService
    ) {}

    public function create(CreateScheduleRequest $request){
        $this->scheduleService->createSchedule($request->validated());

        return response()->json([
            'message' => 'Schedule successfully created.',
        ], 201);
    }
}
