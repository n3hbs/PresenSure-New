<?php

use App\Models\AttendanceSession;
use App\Models\UserCourseBlock;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (string) $user->user_id === (string) $id;
}, ['guards' => ['sanctum']]);

Broadcast::channel('attendance.session.{sessionId}', function ($user, $sessionId) {
    $session = AttendanceSession::find($sessionId);
    if (! $session) {
        return false;
    }

    // 1. Instructor who owns/created the session
    if ((string) $user->user_id === (string) $session->instructor_id) {
        return true;
    }

    // 2. Or student assigned to the schedule's course block
    return UserCourseBlock::where('user_id', $user->user_id)
        ->where('course_block_id', function ($query) use ($session) {
            $query->select('course_block_id')
                ->from('schedules')
                ->where('schedule_id', $session->schedule_id);
        })
        ->exists();
}, ['guards' => ['sanctum']]);
