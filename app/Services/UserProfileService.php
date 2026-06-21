<?php

namespace App\Services;

use App\Repositories\Interfaces\UserProfileRepositoryInterface;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\UploadedFile;

class UserProfileService
{
    public function __construct(
        private UserProfileRepositoryInterface $profileRepository
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
}
