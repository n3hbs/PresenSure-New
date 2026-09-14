<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkImageUploadRequest;
use App\Services\UserProfileService;
use Illuminate\Http\JsonResponse;

class BulkImageUploadController extends Controller
{
    public function __construct(
        private UserProfileService $userProfileService
    ) {}

    public function upload(BulkImageUploadRequest $request): JsonResponse
    {
        $images = $request->file('images', []);
        $type = $request->input('type');
        $overwrite = $request->boolean('overwrite', false);

        $results = $this->userProfileService->bulkUpload($images, $type, $overwrite);

        return response()->json([
            'message' => 'Bulk image upload processed.',
            'data' => $results,
        ]);
    }
}
