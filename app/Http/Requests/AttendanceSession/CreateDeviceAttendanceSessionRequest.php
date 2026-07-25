<?php

namespace App\Http\Requests\AttendanceSession;

use Illuminate\Foundation\Http\FormRequest;

class CreateDeviceAttendanceSessionRequest extends FormRequest
{
    /**
     * Authentication is handled by the route's auth:sanctum middleware.
     * Returning true allows every authenticated user to reach the service,
     * where assignment to the selected course block is checked.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validate the client-controlled values before the controller runs.
     * The authenticated instructor is deliberately not accepted as input;
     * the controller obtains that user from the Sanctum access token.
     */
    public function rules(): array
    {
        return [
            // The schedule must already exist in the schedules table.
            'schedule_id' => 'required|integer|exists:schedules,schedule_id',

            // This is the ESP32 public_device_id, not its numeric database key.
            'device_id' => 'required|string|max:255',

            // Determines which attendance checks the session will require.
            'verification_mode' => 'required|string|in:ble,face,ble_face',

            // Enables repeated attendance checks during the session.
            'continuous_checking' => 'required|boolean',

            // Requested length; the service still caps it at the class end time.
            'requested_duration_minutes' => 'required|integer|min:1|max:1440',
        ];
    }
}
