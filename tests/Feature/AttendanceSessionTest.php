<?php

namespace Tests\Feature;

use App\Models\BleDevice;
use App\Models\Period;
use App\Models\Role;
use App\Models\Schedule;
use App\Models\ScheduleDay;
use App\Models\User;
use App\Models\UserCourseBlock;
use App\Models\UserRole;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use LogicException;
use Psr\Log\AbstractLogger;
use Stringable;
use Tests\TestCase;

class AttendanceSessionTest extends TestCase
{
    use RefreshDatabase;

    private const DEVICE_SECRET = '0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef';

    private User $instructor;

    private Schedule $schedule;

    private Period $period;

    private BleDevice $bleDevice;

    private int $buildingId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse('2026-07-20 08:30:00', config('app.timezone')));
        $this->seedAttendanceSessionDependencies();
        Sanctum::actingAs($this->instructor);
    }

    public function test_instructor_can_create_room_beacon_attendance_session(): void
    {
        $logger = new class extends AbstractLogger
        {
            public array $records = [];

            public function log($level, Stringable|string $message, array $context = []): void
            {
                $this->records[] = compact('level', 'message', 'context');
            }
        };

        $this->app->instance('log', $logger);

        $response = $this->postJson('/api/attendance-session', $this->validPayload());

        $response->assertCreated()
            ->assertJsonPath('message', 'Attendance session created successfully.')
            ->assertJsonPath('data.session.ble_device_id', $this->bleDevice->ble_device_id)
            ->assertJsonPath('data.session.requires_periodic_verification', true)
            ->assertJsonPath('data.beacon_configuration.attendance_type', 3)
            ->assertJsonPath('data.beacon_configuration.continuous', true)
            ->assertJsonPath('data.beacon_configuration.advertisement_interval_ms', 500)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'session' => [
                        'attendance_session_id',
                        'session_code',
                        'schedule_id',
                        'period_id',
                        'instructor_id',
                        'ble_device_id',
                        'verification_mode',
                        'requires_periodic_verification',
                        'ble_token_expires_at',
                        'status',
                        'start_at',
                        'end_at',
                        'created_at',
                        'updated_at',
                    ],
                    'ble_token',
                    'beacon_configuration' => [
                        'session_code',
                        'attendance_type',
                        'start_time',
                        'end_time',
                        'continuous',
                        'rotating_secret',
                        'signature',
                        'advertisement_interval_ms',
                    ],
                ],
            ]);

        $rawToken = $response->json('data.ble_token');
        $sessionCode = $response->json('data.session.session_code');

        $this->assertSame($sessionCode, $response->json('data.beacon_configuration.session_code'));
        $this->assertArrayNotHasKey('ble_broadcast_token', $response->json('data.session'));
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{64}$/',
            $response->json('data.beacon_configuration.signature')
        );
        $this->assertStringNotContainsString(self::DEVICE_SECRET, $response->getContent());
        $this->assertStringNotContainsString(self::DEVICE_SECRET, json_encode($logger->records));
        $this->assertDatabaseHas('attendance_sessions', [
            'session_code' => $sessionCode,
            'ble_device_id' => $this->bleDevice->ble_device_id,
            'ble_broadcast_token' => hash('sha256', $rawToken),
        ]);
        $this->assertNotSame(
            self::DEVICE_SECRET,
            DB::table('ble_devices')->value('device_secret')
        );
    }

    public function test_non_instructor_cannot_create_attendance_session(): void
    {
        UserRole::where('user_id', $this->instructor->user_id)->delete();

        $this->postJson('/api/attendance-session', $this->validPayload())
            ->assertForbidden();

        $this->assertDatabaseCount('attendance_sessions', 0);
    }

    public function test_beacon_is_looked_up_by_public_string_device_id(): void
    {
        $this->assertNotSame(
            (string) $this->bleDevice->ble_device_id,
            $this->bleDevice->public_device_id
        );

        $this->postJson('/api/attendance-session', $this->validPayload())
            ->assertCreated()
            ->assertJsonPath('data.session.ble_device_id', $this->bleDevice->ble_device_id);
    }

    public function test_missing_beacon_is_rejected(): void
    {
        $this->bleDevice->delete();

        $this->postJson('/api/attendance-session', $this->validPayload())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('device_id');

        $this->assertDatabaseCount('attendance_sessions', 0);
    }

    public function test_inactive_beacon_is_rejected(): void
    {
        $this->bleDevice->update(['status' => 'inactive']);

        $this->postJson('/api/attendance-session', $this->validPayload())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('device_id');

        $this->assertDatabaseCount('attendance_sessions', 0);
    }

    public function test_beacon_from_another_room_is_rejected(): void
    {
        $otherRoomId = DB::table('rooms')->insertGetId([
            'building_id' => $this->buildingId,
            'name' => 'Room 202',
            'floor_no' => 2,
            'status' => 'Active',
        ], 'room_id');

        $this->bleDevice->update(['room_id' => $otherRoomId]);

        $this->postJson('/api/attendance-session', $this->validPayload())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('device_id');

        $this->assertDatabaseCount('attendance_sessions', 0);
    }

    public function test_beacon_with_invalid_device_secret_is_rejected_and_session_is_rolled_back(): void
    {
        $this->bleDevice->update(['device_secret' => 'not-a-valid-secret']);

        $this->postJson('/api/attendance-session', $this->validPayload())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('device_id');

        $this->assertDatabaseCount('attendance_sessions', 0);
    }

    public function test_attendance_session_is_rejected_outside_schedule_window(): void
    {
        $this->travelTo(Carbon::parse('2026-07-20 07:59:59', config('app.timezone')));

        $this->postJson('/api/attendance-session', $this->validPayload())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('schedule_id');

        $this->assertDatabaseCount('attendance_sessions', 0);
    }

    public function test_session_end_time_is_capped_at_scheduled_class_end(): void
    {
        $this->travelTo(Carbon::parse('2026-07-20 09:30:00', config('app.timezone')));
        $scheduledEnd = Carbon::parse('2026-07-20 10:00:00', config('app.timezone'));

        $response = $this->postJson('/api/attendance-session', $this->validPayload())
            ->assertCreated();

        $response->assertJsonPath(
            'data.beacon_configuration.end_time',
            $scheduledEnd->timestamp
        );
        $this->assertSame(
            $scheduledEnd->toISOString(),
            $response->json('data.session.end_at')
        );
        $this->assertSame(
            $scheduledEnd->toISOString(),
            $response->json('data.session.ble_token_expires_at')
        );
    }

    public function test_requested_duration_is_used_when_before_class_end(): void
    {
        $payload = $this->validPayload();
        $payload['requested_duration_minutes'] = 30;
        $expectedEnd = Carbon::parse('2026-07-20 09:00:00', config('app.timezone'));

        $response = $this->postJson('/api/attendance-session', $payload)
            ->assertCreated();

        $response->assertJsonPath(
            'data.beacon_configuration.end_time',
            $expectedEnd->timestamp
        );
        $this->assertSame(
            $expectedEnd->toISOString(),
            $response->json('data.session.end_at')
        );
    }

    public function test_configuration_failure_rolls_back_created_session(): void
    {
        config(['beacon.advertisement_interval_ms' => 99]);
        $this->withoutExceptionHandling();

        try {
            $this->postJson('/api/attendance-session', $this->validPayload());
            $this->fail('Expected invalid beacon configuration to fail.');
        } catch (LogicException $exception) {
            $this->assertSame(
                'The BLE advertisement interval must be between 100 and 5000 milliseconds.',
                $exception->getMessage()
            );
        }

        $this->assertDatabaseCount('attendance_sessions', 0);
    }

    private function validPayload(): array
    {
        return [
            'schedule_id' => $this->schedule->schedule_id,
            'device_id' => $this->bleDevice->public_device_id,
            'verification_mode' => 'ble_face',
            'continuous_checking' => true,
            'requested_duration_minutes' => 120,
        ];
    }

    private function seedAttendanceSessionDependencies(): void
    {
        $this->instructor = User::factory()->create(['user_id' => '2000-0001']);

        $instructorRole = Role::create([
            'role_name' => 'instructor',
            'description' => 'Creates and manages attendance sessions.',
        ]);

        UserRole::create([
            'user_id' => $this->instructor->user_id,
            'role_id' => $instructorRole->role_id,
            'assigned_at' => now(),
        ]);

        $schoolYearId = DB::table('school_years')->insertGetId([
            'school_year_start' => '2026-06-01',
            'school_year_end' => '2027-05-31',
        ], 'school_year_id');

        $semesterId = DB::table('semesters')->insertGetId([
            'school_year_id' => $schoolYearId,
            'term' => 'First Semester',
            'semester_start' => '2026-06-01',
            'semester_end' => '2026-10-31',
        ], 'semester_id');

        $courseId = DB::table('courses')->insertGetId([
            'subject_code' => 'IT101',
            'name' => 'Introduction to Information Technology',
        ], 'course_id');

        $courseBlockId = DB::table('course_blocks')->insertGetId([
            'course_id' => $courseId,
            'semester_id' => $semesterId,
            'block_code' => 'BSIT-1A',
        ], 'course_block_id');

        $this->buildingId = DB::table('buildings')->insertGetId([
            'code' => 'MAIN',
            'name' => 'Main Building',
        ], 'building_id');

        $roomId = DB::table('rooms')->insertGetId([
            'building_id' => $this->buildingId,
            'name' => 'Room 101',
            'floor_no' => 1,
            'capacity' => 40,
            'status' => 'Active',
        ], 'room_id');

        $this->schedule = Schedule::create([
            'course_block_id' => $courseBlockId,
            'room_id' => $roomId,
            'semester_id' => $semesterId,
            'block_code' => 'BSIT-1A',
            'schedule_type' => 'lecture',
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
        ]);

        ScheduleDay::create([
            'schedule_id' => $this->schedule->schedule_id,
            'day' => 'monday',
            'assigned_at' => now(),
        ]);

        $this->period = Period::create([
            'semester_id' => $semesterId,
            'name' => 'prelim',
            'description' => 'Preliminary period',
            'period_start' => '2026-06-01',
            'period_end' => '2026-07-31',
        ]);

        UserCourseBlock::create([
            'user_id' => $this->instructor->user_id,
            'course_block_id' => $courseBlockId,
            'assigned_at' => now(),
        ]);

        $this->bleDevice = BleDevice::create([
            'public_device_id' => 'PS-1234ABCD',
            'room_id' => $roomId,
            'device_secret' => self::DEVICE_SECRET,
            'status' => 'active',
        ]);
    }
}
