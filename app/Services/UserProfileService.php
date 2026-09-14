<?php

namespace App\Services;

use App\Repositories\Interfaces\UserProfileRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\UploadedFile;

class UserProfileService
{
    public function __construct(
        private UserProfileRepositoryInterface $profileRepository,
        private UserRepositoryInterface $userRepository
    ) {}

    public function uploadProfile(?UploadedFile $image, string $userId)
    {
        if (!$image) {
            return null;
        }

        $uploaded = Cloudinary::uploadApi()->upload(
            $image->getRealPath(),
            ['folder' => 'profiles']
        );

        return $this->profileRepository->updateOrCreateByUserId(
            $userId,
            ['imagelink' => $uploaded['secure_url']]
        );
    }

    public function bulkUpload(array $images, ?string $type = null, bool $overwrite = false): array
    {
        $results = [
            'success' => [],
            'failed' => [
                'invalid_name_format' => [],
                'invalid_format' => [],
                'user_not_found' => [],
                'profile_exists' => [],
                'upload_failed' => [],
            ],
            'summary' => [
                'total' => count($images),
                'successful' => 0,
                'failed' => 0,
            ],
        ];

        $validExtensions = ['jpg', 'jpeg', 'png', 'webp'];

        foreach ($images as $image) {
            if (!$image instanceof UploadedFile) {
                continue;
            }

            $originalName = $image->getClientOriginalName();
            $extension = strtolower($image->getClientOriginalExtension());
            $userId = strtoupper(pathinfo($originalName, PATHINFO_FILENAME));

            if (!in_array($extension, $validExtensions)) {
                $results['failed']['invalid_format'][] = [
                    'user_id' => $userId,
                    'fullname' => null,
                    'status' => 'Invalid File Format',
                ];
                $results['summary']['failed']++;
                continue;
            }

            // Validate that the filename strictly matches the User ID format (e.g., C-0000-0000 or 0000-0000)
            if (!preg_match('/^(?:[A-Z]-)?\d{4}-\d{4}$/', $userId)) {
                $results['failed']['invalid_name_format'][] = [
                    'user_id' => $userId,
                    'fullname' => null,
                    'role' => 'Unknown',
                    'status' => 'Invalid ID Format',
                    'detail' => 'Filename must be formatted as C-0000-0000 or 0000-0000',
                ];
                $results['summary']['failed']++;
                continue;
            }

            $user = $this->userRepository->findByUserId($userId);

            if (!$user) {
                $results['failed']['user_not_found'][] = [
                    'user_id' => $userId,
                    'fullname' => null,
                    'role' => 'Unknown',
                    'status' => 'User Not Found',
                ];
                $results['summary']['failed']++;
                continue;
            }

            $fullNameParts = array_filter([
                $user->last_name . ',',
                $user->first_name,
                $user->middle_initial ? $user->middle_initial . '.' : null,
                $user->suffix,
            ]);
            $fullName = implode(' ', $fullNameParts);

            $isStudent = $user->student()->exists();
            $isInstructor = $user->instructor()->exists();
            $userRole = $isStudent ? 'Student' : ($isInstructor ? 'Instructor' : 'User');

            // Must be a registered student or instructor
            if (!$isStudent && !$isInstructor) {
                $results['failed']['user_not_found'][] = [
                    'user_id' => $userId,
                    'fullname' => $fullName,
                    'role' => 'User',
                    'status' => 'User is not a student or instructor',
                ];
                $results['summary']['failed']++;
                continue;
            }

            if (!$overwrite && $user->userProfile && !empty($user->userProfile->imagelink)) {
                $results['failed']['profile_exists'][] = [
                    'user_id' => $userId,
                    'fullname' => $fullName,
                    'role' => $userRole,
                    'status' => 'Profile Already Exists',
                ];
                $results['summary']['failed']++;
                continue;
            }

            try {
                $uploaded = Cloudinary::uploadApi()->upload(
                    $image->getRealPath(),
                    ['folder' => 'profiles']
                );

                $imageUrl = $uploaded['secure_url'];

                $this->profileRepository->updateOrCreateByUserId(
                    $userId,
                    ['imagelink' => $imageUrl]
                );

                $results['success'][] = [
                    'user_id' => $userId,
                    'fullname' => $fullName,
                    'role' => $userRole,
                    'image_url' => $imageUrl,
                    'status' => 'Uploaded',
                ];
                $results['summary']['successful']++;
            } catch (\Exception $e) {
                $results['failed']['upload_failed'][] = [
                    'user_id' => $userId,
                    'fullname' => $fullName,
                    'role' => $userRole,
                    'status' => 'Upload Failed',
                    'error' => $e->getMessage(),
                ];
                $results['summary']['failed']++;
            }
        }

        return $results;
    }
}
