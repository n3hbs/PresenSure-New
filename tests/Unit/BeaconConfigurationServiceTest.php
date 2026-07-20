<?php

namespace Tests\Unit;

use App\Models\AttendanceSession;
use App\Models\BleDevice;
use App\Services\BeaconConfigurationService;
use Carbon\Carbon;
use Tests\TestCase;

class BeaconConfigurationServiceTest extends TestCase
{
    private const DEVICE_SECRET = '000102030405060708090a0b0c0d0e0f101112131415161718191a1b1c1d1e1f';

    public function test_it_uses_exact_canonical_payload_and_raw_decoded_hmac_key(): void
    {
        config(['beacon.advertisement_interval_ms' => 500]);

        $session = $this->makeAttendanceSession('ble_face');
        $device = new BleDevice(['device_secret' => self::DEVICE_SECRET]);
        $rotatingSecret = 'deterministic-rotating-secret';
        $service = new BeaconConfigurationService;

        $configuration = $service->generate($session, $device, $rotatingSecret);
        $expectedCanonicalPayload = implode('|', [
            '018f3f34-91ab-7abc-8def-0123456789ab',
            '3',
            '1784512800',
            '1784516400',
            '1',
            $rotatingSecret,
            '500',
        ]);
        $expectedSignature = hash_hmac(
            'sha256',
            $expectedCanonicalPayload,
            hex2bin(self::DEVICE_SECRET),
            false
        );
        $incorrectHexTextKeySignature = hash_hmac(
            'sha256',
            $expectedCanonicalPayload,
            self::DEVICE_SECRET,
            false
        );

        $this->assertSame($expectedCanonicalPayload, $service->canonicalPayload(
            $configuration['session_id'],
            $configuration['attendance_type'],
            $configuration['start_time'],
            $configuration['end_time'],
            $configuration['continuous'],
            $configuration['rotating_secret'],
            $configuration['advertisement_interval_ms']
        ));
        $this->assertSame($expectedSignature, $configuration['signature']);
        $this->assertNotSame($incorrectHexTextKeySignature, $configuration['signature']);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $configuration['signature']);
    }

    public function test_it_maps_each_verification_mode_to_firmware_attendance_type(): void
    {
        $service = new BeaconConfigurationService;
        $device = new BleDevice(['device_secret' => self::DEVICE_SECRET]);

        foreach (['ble' => 1, 'face' => 2, 'ble_face' => 3] as $mode => $expectedType) {
            $configuration = $service->generate($this->makeAttendanceSession($mode), $device, 'fixed-secret');

            $this->assertSame($expectedType, $configuration['attendance_type']);
        }
    }

    private function makeAttendanceSession(string $verificationMode): AttendanceSession
    {
        return new AttendanceSession([
            'session_uuid' => '018f3f34-91ab-7abc-8def-0123456789ab',
            'verification_mode' => $verificationMode,
            'requires_periodic_verification' => true,
            'start_at' => Carbon::createFromTimestamp(1784512800, config('app.timezone')),
            'end_at' => Carbon::createFromTimestamp(1784516400, config('app.timezone')),
        ]);
    }
}
