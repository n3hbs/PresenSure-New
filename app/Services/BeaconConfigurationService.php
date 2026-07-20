<?php

namespace App\Services;

use App\Models\AttendanceSession;
use App\Models\BleDevice;
use Illuminate\Validation\ValidationException;
use LogicException;

class BeaconConfigurationService
{
    public function generate(
        AttendanceSession $session,
        BleDevice $bleDevice,
        ?string $rotatingSecret = null
    ): array {
        $advertisementIntervalMs = (int) config('beacon.advertisement_interval_ms', 500);

        if ($advertisementIntervalMs < 100 || $advertisementIntervalMs > 5000) {
            throw new LogicException('The BLE advertisement interval must be between 100 and 5000 milliseconds.');
        }

        $secretHex = strtolower(trim((string) $bleDevice->device_secret));

        if (strlen($secretHex) !== 64 || ! ctype_xdigit($secretHex)) {
            throw ValidationException::withMessages([
                'beacon_id' => ['The selected ESP32 does not have a valid provisioned device secret.'],
            ]);
        }

        $secretBytes = hex2bin($secretHex);
        $attendanceType = $this->attendanceType($session->verification_mode);
        $startTime = $session->start_at->timestamp;
        $endTime = $session->end_at->timestamp;
        $continuous = $session->requires_periodic_verification;
        $rotatingSecret ??= bin2hex(random_bytes(32));

        $canonicalPayload = $this->canonicalPayload(
            $session->session_uuid,
            $attendanceType,
            $startTime,
            $endTime,
            $continuous,
            $rotatingSecret,
            $advertisementIntervalMs
        );

        return [
            'session_id' => $session->session_uuid,
            'attendance_type' => $attendanceType,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'continuous' => $continuous,
            'rotating_secret' => $rotatingSecret,
            'signature' => hash_hmac('sha256', $canonicalPayload, $secretBytes, false),
            'advertisement_interval_ms' => $advertisementIntervalMs,
        ];
    }

    public function canonicalPayload(
        string $sessionId,
        int $attendanceType,
        int $startTime,
        int $endTime,
        bool $continuous,
        string $rotatingSecret,
        int $advertisementIntervalMs
    ): string {
        return implode('|', [
            $sessionId,
            (string) $attendanceType,
            (string) $startTime,
            (string) $endTime,
            $continuous ? '1' : '0',
            $rotatingSecret,
            (string) $advertisementIntervalMs,
        ]);
    }

    private function attendanceType(string $verificationMode): int
    {
        return match ($verificationMode) {
            'ble' => 1,
            'face' => 2,
            'ble_face' => 3,
            default => throw new LogicException('Unsupported attendance verification mode.'),
        };
    }
}
