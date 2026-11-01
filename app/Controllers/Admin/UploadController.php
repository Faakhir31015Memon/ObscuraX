<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Upload;

final class UploadController extends Controller
{
    public function image(): void
    {
        try {
            $path = Upload::image($_FILES['image'] ?? null, 'blogs', 5);
        } catch (\RuntimeException $e) {
            json_response(['ok' => false, 'message' => $e->getMessage()], 422);
        }

        json_response(['ok' => true, 'path' => $path, 'url' => uploaded_url($path)]);
    }
}
