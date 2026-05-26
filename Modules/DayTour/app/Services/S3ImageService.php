<?php

namespace Modules\DayTour\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\DayTour\Models\DayTourImage;

class S3ImageService
{
    private const STORAGE_DISK = 's3';
    private const IMAGE_PATH = 'day-tours';

    /**
     * Upload image to S3 and create database record
     */
    public function uploadImage(string $dayTourId, UploadedFile $file, bool $isPrimary = false): DayTourImage
    {
        // Generate unique filename
        $filename = sprintf(
            '%s_%s.%s',
            Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)),
            Str::random(8),
            $file->getClientOriginalExtension()
        );

        // Upload to S3
        $path = Storage::disk(self::STORAGE_DISK)->putFileAs(
            self::IMAGE_PATH . '/' . $dayTourId,
            $file,
            $filename,
            'public'
        );

        $url = Storage::disk(self::STORAGE_DISK)->url($path);

        // Create database record
        $image = DayTourImage::create([
            'day_tour_id' => $dayTourId,
            's3_path' => $url,
            'filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'disk' => self::STORAGE_DISK,
            'is_primary' => $isPrimary,
        ]);

        // If primary, unmark others
        if ($isPrimary) {
            $image->markAsPrimary();
        }

        return $image;
    }

    /**
     * Delete image from S3 and database
     */
    public function deleteImage(DayTourImage $image): bool
    {
        // Extract path from S3 URL if needed
        $path = $this->extractPathFromUrl($image->s3_path);

        // Delete from S3
        if (Storage::disk(self::STORAGE_DISK)->exists($path)) {
            Storage::disk(self::STORAGE_DISK)->delete($path);
        }

        // Delete from database
        $image->delete();

        return true;
    }

    /**
     * Extract path from S3 URL
     */
    private function extractPathFromUrl(string $url): string
    {
        // If URL, extract the path
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $parts = parse_url($url);
            return ltrim($parts['path'] ?? '', '/');
        }

        return $url;
    }

    /**
     * Generate thumbnail URL for image (CloudFront/CDN)
     */
    public function getThumbnailUrl(string $imageUrl, int $width = 300, int $height = 300): string
    {
        // If using CloudFront with image optimization
        return $imageUrl . '?width=' . $width . '&height=' . $height . '&fit=cover';
    }

    /**
     * Batch delete images
     */
    public function deleteBatch(array $imageIds): int
    {
        $images = DayTourImage::whereIn('id', $imageIds)->get();
        $deleted = 0;

        foreach ($images as $image) {
            if ($this->deleteImage($image)) {
                $deleted++;
            }
        }

        return $deleted;
    }
}
