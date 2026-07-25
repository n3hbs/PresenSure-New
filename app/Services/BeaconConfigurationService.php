<?php

namespace App\Services;

use App\Models\AttendanceSession;
use App\Models\BleDevice;
use Illuminate\Validation\ValidationException;
use LogicException;

class BeaconConfigurationService
{
    /**
     * Build the initial signed configuration that a relay can pass to an ESP32.
     */
    public function generate(AttendanceSession $session, BleDevice $bleDevice, ?string $rotatingSecret = null): array
    {
        // Read the broadcast frequency from config, with 500 ms as the fallback.
        $advertisementIntervalMs = (int) config('beacon.advertisement_interval_ms', 500);

        // Reject values outside the range supported by the intended firmware.
        if ($advertisementIntervalMs < 100 || $advertisementIntervalMs > 5000) {
            throw new LogicException('The BLE advertisement interval must be between 100 and 5000 milliseconds.');
        }

        // Prepare values in the primitive types expected by ESP32 firmware.
        $secretBytes = $this->deviceSecretBytes($bleDevice);
        $attendanceType = $this->attendanceType($session->verification_mode);
        $startTime = $session->start_at->timestamp;
        $endTime = $session->end_at->timestamp;
        $continuous = $session->requires_periodic_verification;

        // Tests may supply a fixed secret; production generates a random one.
        $rotatingSecret ??= bin2hex(random_bytes(32));

        // Produce the exact text that will be authenticated by HMAC-SHA256.
        $canonicalPayload = $this->canonicalPayload(
            $session->session_code,
            $attendanceType,
            $startTime,
            $endTime,
            $continuous,
            $rotatingSecret,
            $advertisementIntervalMs
        );

        // Return configuration values plus their tamper-detection signature.
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

    /**
     * Join fields in a stable order so PHP and the ESP32 sign identical bytes.
     */
    public function canonicalPayload(string $sessionCode, int $attendanceType, int $startTime, int $endTime, bool $continuous, string $rotatingSecret, int $advertisementIntervalMs): string
    {
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

    /**
     * Translate API verification names into numeric firmware values.
     */
    private function attendanceType(string $verificationMode): int
    {
        return match ($verificationMode) {
            'ble' => 1,
            'face' => 2,
            'ble_face' => 3,
            default => throw new LogicException('Unsupported attendance verification mode.'),
        };
    }

    /**
     * Validate and decode the device-specific 32-byte hexadecimal HMAC key.
     */
    private function deviceSecretBytes(BleDevice $bleDevice): string
    {
        // Normalize the decrypted secret before validating its format.
        $secretHex = strtolower(trim((string) $bleDevice->device_secret));

        // A 32-byte key must contain exactly 64 hexadecimal characters.
        if (strlen($secretHex) !== 64 || ! ctype_xdigit($secretHex)) {
            throw ValidationException::withMessages([
                'device_id' => ['The selected ESP32 does not have a valid device secret.'],
            ]);
        }

        // hash_hmac() must receive decoded bytes, not the visible hex text.
        return hex2bin($secretHex);
    }
}
