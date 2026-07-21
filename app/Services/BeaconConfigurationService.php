<?php

namespace App\Services;

use App\Models\AttendanceSession;
use App\Models\BleDevice;
use Illuminate\Validation\ValidationException;
use LogicException;

class BeaconConfigurationService
{
    public function generateBlePayload(
        AttendanceSession $session,
        BleDevice $bleDevice,
        string $rotatingToken
    ): array {
        $secretBytes = $this->deviceSecretBytes($bleDevice);
        $issuedAt = $session->start_at->timestamp;
        $expiresAt = $session->end_at->timestamp;
        $canonicalPayload = implode('|', [
            $session->session_code,
            $rotatingToken,
            (string) $issuedAt,
            (string) $expiresAt,
        ]);

        return [
            'session_code' => $session->session_code,
            'rotating_token' => $rotatingToken,
            'issued_at' => $issuedAt,
            'expires_at' => $expiresAt,
            'signature' => hash_hmac('sha256', $canonicalPayload, $secretBytes),
        ];
    }

    public function generate(
        AttendanceSession $session,
        BleDevice $bleDevice,
        ?string $rotatingSecret = null
    ): array {
        $advertisementIntervalMs = (int) config('beacon.advertisement_interval_ms', 500);

        if ($advertisementIntervalMs < 100 || $advertisementIntervalMs > 5000) {
            throw new LogicException('The BLE advertisement interval must be between 100 and 5000 milliseconds.');
        }

        $secretBytes = $this->deviceSecretBytes($bleDevice);
        $attendanceType = $this->attendanceType($session->verification_mode);
        $startTime = $session->start_at->timestamp;
        $endTime = $session->end_at->timestamp;
        $continuous = $session->requires_periodic_verification;
        $rotatingSecret ??= bin2hex(random_bytes(32));

        $canonicalPayload = $this->canonicalPayload(
            $session->session_code,
            $attendanceType,
            $startTime,
            $endTime,
            $continuous,
            $rotatingSecret,
            $advertisementIntervalMs
        );

        return [
            'session_code' => $session->session_code,
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
        string $sessionCode,
        int $attendanceType,
        int $startTime,
        int $endTime,
        bool $continuous,
        string $rotatingSecret,
        int $advertisementIntervalMs
    ): string {
        return implode('|', [
            $sessionCode,
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

    private function deviceSecretBytes(BleDevice $bleDevice): string
    {
        $secretHex = strtolower(trim((string) $bleDevice->device_secret));

        if (strlen($secretHex) !== 64 || ! ctype_xdigit($secretHex)) {
            throw ValidationException::withMessages([
                'device_id' => ['The selected ESP32 does not have a valid device secret.'],
            ]);
        }

        return hex2bin($secretHex);
    }
}
