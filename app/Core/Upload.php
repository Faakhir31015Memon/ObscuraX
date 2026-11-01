<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class Upload
{
    private const ERROR_MESSAGES = [
        UPLOAD_ERR_INI_SIZE => 'The file is larger than the server allows.',
        UPLOAD_ERR_FORM_SIZE => 'The file is too large.',
        UPLOAD_ERR_PARTIAL => 'The file only partially uploaded. Please try again.',
        UPLOAD_ERR_NO_FILE => 'Please choose a file to upload.',
        UPLOAD_ERR_NO_TMP_DIR => 'Server misconfiguration: missing temporary folder.',
        UPLOAD_ERR_CANT_WRITE => 'Server could not write the file to disk.',
        UPLOAD_ERR_EXTENSION => 'The upload was blocked by a server extension.',
    ];

    /**
     * Validates and stores an uploaded image. Returns the path relative to /uploads.
     *
     * @param array{name?:string,type?:string,tmp_name?:string,error?:int,size?:int}|null $file
     */
    public static function image(?array $file, string $folder = 'blogs', ?int $maxMb = null): string
    {
        if ($file === null || !isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            throw new RuntimeException('Please choose an image file.');
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException(self::ERROR_MESSAGES[$file['error']] ?? 'The upload failed.');
        }

        $maxMb ??= (int) config('uploads.max_image_mb', 5);
        if (($file['size'] ?? 0) <= 0) {
            throw new RuntimeException('The uploaded file is empty.');
        }
        if ($file['size'] > $maxMb * 1024 * 1024) {
            throw new RuntimeException('Images must be smaller than ' . $maxMb . ' MB.');
        }

        $tmp = $file['tmp_name'] ?? '';
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            throw new RuntimeException('Invalid upload source.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = (string) $finfo->file($tmp);
        $allowed = config('uploads.image_mimes', []);
        if (!isset($allowed[$mime])) {
            throw new RuntimeException('Only JPG, PNG, WEBP and GIF images are allowed.');
        }

        $imageInfo = @getimagesize($tmp);
        if ($imageInfo === false || $imageInfo[0] < 10 || $imageInfo[1] < 10) {
            throw new RuntimeException('The file is not a valid image.');
        }
        if ($imageInfo[0] > 6000 || $imageInfo[1] > 6000) {
            throw new RuntimeException('Images must be at most 6000×6000 pixels.');
        }

        $extension = $allowed[$mime];
        $folder = trim(preg_replace('/[^a-z0-9_\-]/i', '', $folder) ?? 'general', '/');
        $relativeDir = $folder . '/' . date('Y/m');
        $absoluteDir = ROOT_PATH . '/uploads/' . $relativeDir;

        if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0755, true) && !is_dir($absoluteDir)) {
            throw new RuntimeException('Could not create the upload directory.');
        }

        $filename = date('Ymd-His') . '-' . bin2hex(random_bytes(6)) . '.' . $extension;
        if (!move_uploaded_file($tmp, $absoluteDir . '/' . $filename)) {
            throw new RuntimeException('Could not save the uploaded image.');
        }

        @chmod($absoluteDir . '/' . $filename, 0644);

        return $relativeDir . '/' . $filename;
    }

    public static function delete(?string $relativePath): void
    {
        if ($relativePath === null || $relativePath === '' || str_contains($relativePath, '..')) {
            return;
        }
        $absolute = ROOT_PATH . '/uploads/' . ltrim($relativePath, '/');
        $real = realpath($absolute);
        $uploadsRoot = realpath(ROOT_PATH . '/uploads');
        if ($real !== false && $uploadsRoot !== false && str_starts_with($real, $uploadsRoot) && is_file($real)) {
            @unlink($real);
        }
    }
}
