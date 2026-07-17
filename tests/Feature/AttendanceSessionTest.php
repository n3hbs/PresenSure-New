<?php

namespace Tests\Feature;

use App\Models\AttendanceSession;
use App\Models\Period;
use App\Models\Schedule;
use App\Models\User;
use App\Models\UserCourseBlock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AttendanceSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_instructor_can_create_attendance_session(): void
    {
        $instructor = User::factory()->create([
            'instructor_id' => 2,
            'user_id' => '2000-0001',
        ]);

        $schedule = Schedule::factory()->create();

        $period = Period::factory()->create([
            'semester_id' => $schedule->semester_id,
        ]);

        UserCourseBlock::create([
            'user_id' => $instructor->user_id,
            'course_block_id' => $schedule->course_block_id,
            'assigned_at' => now(),
        ]);

        Sanctum::actingAs($instructor);

        $response = $this->postJson('/api/attendance-session', [
            'schedule_id' => $schedule->schedule_id,
            'period_id' => $period->period_id,
            'verification_mode' => 'ble_face',
            'ble_source_type' => 'instructor_phone',
            'beacon_id' => null,
        ]);

        $response->assertCreated()
            ->assertJsonPath('message', 'Attendance session created successfully.')
            ->assertJsonStructure([
                'message',
                'data' => [
                    'session',
                    'ble_token',
                ],
            ]);

        $this->assertDatabaseHas('attendance_sessions', [
            'schedule_id' => $schedule->schedule_id,
            'period_id' => $period->period_id,
            'instructor_id' => $instructor->user_id,
            'verification_mode' => 'ble_face',
            'ble_source_type' => 'instructor_phone',
            'status' => 'active',
        ]);

        $this->assertNotNull($response->json('data.ble_token'));
    }
}