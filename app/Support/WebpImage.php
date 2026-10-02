<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WebpImage
{
    /**
     * Convert an uploaded image to WebP and store it on the public disk.
     * GIFs are stored as-is so animation is preserved (GD WebP export is single-frame).
     * Falls back to the original format if conversion is not possible.
     */
    public static function store(UploadedFile $file, string $directory = 'products', int $quality = 82, int $maxWidth = 1920): string
    {
        $directory = trim($directory, '/');
        $mime = strtolower((string) $file->getMimeType());
        $extension = strtolower((string) $file->getClientOriginalExtension());

        // Keep animated GIFs intact — converting via GD drops all frames after the first.
        if ($mime === 'image/gif' || $extension === 'gif') {
            return self::storeOriginalAs($file, $directory, 'gif');
        }

        if (!function_exists('imagewebp') || !self::canDecode($file)) {
            return $file->store($directory, 'public');
        }

        $source = self::createImageResourceFromPath($file->getRealPath(), (string) $file->getMimeType());
        if ($source === null) {
            return $file->store($directory, 'public');
        }

        return self::encodeAndStore($source, $directory, $quality, $maxWidth)
            ?? $file->store($directory, 'public');
    }

    /**
     * Store the original file bytes with a stable UUID filename.
     */
    protected static function storeOriginalAs(UploadedFile $file, string $directory, string $extension): string
    {
        $path = $directory . '/' . Str::uuid()->toString() . '.' . ltrim($extension, '.');
        Storage::disk('public')->put($path, file_get_contents($file->getRealPath()));

        return $path;
    }

    /**
     * Convert raw image binary (e.g. AI-generated PNG) to WebP and store it.
     */
    public static function storeBinary(string $binary, string $directory = 'products', int $quality = 82, int $maxWidth = 1920, string $fallbackExt = 'png'): string
    {
        $directory = trim($directory, '/');
        $fallbackPath = $directory . '/' . Str::uuid()->toString() . '.' . ltrim($fallbackExt, '.');

        if (!function_exists('imagewebp') || $binary === '') {
            Storage::disk('public')->put($fallbackPath, $binary);
            return $fallbackPath;
        }

        $tmp = tempnam(sys_get_temp_dir(), 'img_');
        if ($tmp === false) {
            Storage::disk('public')->put($fallbackPath, $binary);
            return $fallbackPath;
        }

        file_put_contents($tmp, $binary);
        $mime = mime_content_type($tmp) ?: 'image/png';
        $source = self::createImageResourceFromPath($tmp, $mime);
        @unlink($tmp);

        if ($source === null) {
            Storage::disk('public')->put($fallbackPath, $binary);
            return $fallbackPath;
        }

        $path = self::encodeAndStore($source, $directory, $quality, $maxWidth);
        if ($path === null) {
            Storage::disk('public')->put($fallbackPath, $binary);
            return $fallbackPath;
        }

        return $path;
    }

    /**
     * @param \GdImage|resource $source
     */
    protected static function encodeAndStore($source, string $directory, int $quality, int $maxWidth): ?string
    {
        $width = imagesx($source);
        $height = imagesy($source);

        if ($width > $maxWidth) {
            $newHeight = (int) round($height * ($maxWidth / $width));
            $resized = imagecreatetruecolor($maxWidth, $newHeight);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
            imagefilledrectangle($resized, 0, 0, $maxWidth, $newHeight, $transparent);
            imagecopyresampled($resized, $source, 0, 0, 0, 0, $maxWidth, $newHeight, $width, $height);
            imagedestroy($source);
            $source = $resized;
        }

        // Flatten onto white so photos keep clean WebP output
        $flat = imagecreatetruecolor(imagesx($source), imagesy($source));
        $white = imagecolorallocate($flat, 255, 255, 255);
        imagefilledrectangle($flat, 0, 0, imagesx($source), imagesy($source), $white);
        imagealphablending($flat, true);
        imagecopy($flat, $source, 0, 0, 0, 0, imagesx($source), imagesy($source));
        imagedestroy($source);

        ob_start();
        $ok = imagewebp($flat, null, $quality);
        $webpBinary = ob_get_clean();
        imagedestroy($flat);

        if (!$ok || $webpBinary === false || $webpBinary === '') {
            return null;
        }

        $path = $directory . '/' . Str::uuid()->toString() . '.webp';
        Storage::disk('public')->put($path, $webpBinary);

        return $path;
    }

    protected static function canDecode(UploadedFile $file): bool
    {
        $mime = strtolower((string) $file->getMimeType());

        return in_array($mime, [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/bmp',
        ], true);
    }

    /**
     * @return \GdImage|resource|null
     */
    protected static function createImageResourceFromPath(?string $path, string $mime)
    {
        if (!$path || !is_readable($path)) {
            return null;
        }

        $mime = strtolower($mime);

        try {
            $image = match (true) {
                in_array($mime, ['image/jpeg', 'image/jpg'], true) => @imagecreatefromjpeg($path),
                $mime === 'image/png' => @imagecreatefrompng($path),
                $mime === 'image/gif' => @imagecreatefromgif($path),
                $mime === 'image/webp' && function_exists('imagecreatefromwebp') => @imagecreatefromwebp($path),
                $mime === 'image/bmp' && function_exists('imagecreatefrombmp') => @imagecreatefrombmp($path),
                default => false,
            };

            return $image ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
