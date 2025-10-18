<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileUploadHelper
{
    /**
     * Handle single image upload and return the public URL.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string  $folder
     * @return string|null
     */
    public static function uploadImage(UploadedFile $file, string $folder = 'uploads'): ?string
    {
        if (!$file->isValid()) {
            return null;
        }

        $path = $file->store($folder, 'public');

        return asset('storage/' . $path);
    }

    /**
     * Handle multiple image uploads and return an array of public URLs.
     *
     * @param  array<int, \Illuminate\Http\UploadedFile>  $files
     * @param  string  $folder
     * @return array
     */
    public static function uploadMultipleImages(array $files, string $folder = 'public'): array
    {
        $urls = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile && $file->isValid()) {
                $path = $file->store($folder, 'public');
                $urls[] = asset('storage/' . $path);
            }
        }

        return $urls;
    }

    /**
     * Delete an image from storage if it exists.
     *
     * @param  string  $url
     * @return bool
     */
    public static function deleteImage(string $url): bool
    {
        // Convert the public URL back to the relative path in storage
        $relativePath = str_replace(asset('storage') . '/', '', $url);

        if (Storage::disk('public')->exists($relativePath)) {
            return Storage::disk('public')->delete($relativePath);
        }

        return false;
    }
}
