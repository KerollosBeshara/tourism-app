<?php

namespace Modules\Core\Services;

use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ImageService
{
    private const STORAGE_DISK = 's3';
    private const MAX_FILE_SIZE = 10485760; // 10MB
    private const QUALITY_HIGH = 90;
    private const QUALITY_MEDIUM = 80;
    private const QUALITY_LOW = 70;

    /**
     * Validate image file
     */
    public function validate(UploadedFile $file): bool
    {
        if (!$file->isValid()) {
            throw new InvalidArgumentException('Invalid file upload');
        }

        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new InvalidArgumentException('File size exceeds 10MB limit');
        }

        $mime = $file->getMimeType();
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        if (!in_array($mime, $allowed)) {
            throw new InvalidArgumentException('Invalid image format');
        }

        return true;
    }

    /**
     * Process, Optimize, and Upload an image with variants
     * 
     * Creates: original (optimized), thumbnail (300x300), medium (800x800)
     */
    public function uploadAndOptimize(
        string $folder, 
        string $ownerId, 
        UploadedFile $file, 
        int $width = 1200, 
        int $height = 800,
        bool $createVariants = true
    ): array {
        $this->validate($file);

        // 1. Process original image
        $image = Image::read($file)
            ->cover($width, $height)
            ->toWebp(self::QUALITY_HIGH);

        // 2. Generate filename
        $filename = Str::random(12) . '.webp';
        $fullPath = "{$folder}/{$ownerId}/{$filename}";

        // 3. Upload original
        Storage::disk(self::STORAGE_DISK)->put($fullPath, $image->toString());

        $result = [
            'original' => [
                'url' => Storage::disk(self::STORAGE_DISK)->url($fullPath),
                's3_path' => $fullPath,
                'filename' => $file->getClientOriginalName(),
                'mime_type' => 'image/webp',
                'file_size' => strlen($image->toString()),
                'disk' => self::STORAGE_DISK,
            ],
        ];

        // 4. Create variants if requested
        if ($createVariants) {
            // Thumbnail (300x300)
            $thumbnail = Image::read($file)
                ->cover(300, 300)
                ->toWebp(self::QUALITY_MEDIUM);

            $thumbnailPath = str_replace('.webp', '_thumbnail.webp', $fullPath);
            Storage::disk(self::STORAGE_DISK)->put($thumbnailPath, $thumbnail->toString());

            $result['thumbnail'] = [
                'url' => Storage::disk(self::STORAGE_DISK)->url($thumbnailPath),
                's3_path' => $thumbnailPath,
                'file_size' => strlen($thumbnail->toString()),
            ];

            // Medium (800x800)
            $medium = Image::read($file)
                ->cover(800, 800)
                ->toWebp(self::QUALITY_MEDIUM);

            $mediumPath = str_replace('.webp', '_medium.webp', $fullPath);
            Storage::disk(self::STORAGE_DISK)->put($mediumPath, $medium->toString());

            $result['medium'] = [
                'url' => Storage::disk(self::STORAGE_DISK)->url($mediumPath),
                's3_path' => $mediumPath,
                'file_size' => strlen($medium->toString()),
            ];
        }

        return $result;
    }

    /**
     * Delete an image and all its variants
     */
    public function deleteFromS3(string $path): bool
    {
        $deleted = true;

        // Delete main image
        if (Storage::disk(self::STORAGE_DISK)->exists($path)) {
            $deleted = Storage::disk(self::STORAGE_DISK)->delete($path) && $deleted;
        }

        // Delete thumbnail
        $thumbnail = str_replace('.webp', '_thumbnail.webp', $path);
        if (Storage::disk(self::STORAGE_DISK)->exists($thumbnail)) {
            $deleted = Storage::disk(self::STORAGE_DISK)->delete($thumbnail) && $deleted;
        }

        // Delete medium
        $medium = str_replace('.webp', '_medium.webp', $path);
        if (Storage::disk(self::STORAGE_DISK)->exists($medium)) {
            $deleted = Storage::disk(self::STORAGE_DISK)->delete($medium) && $deleted;
        }

        return $deleted;
    }

    /**
     * Get image metadata
     */
    public function getImageMetadata(string $imagePath): array
    {
        try {
            $image = Image::read(Storage::disk(self::STORAGE_DISK)->get($imagePath));

            return [
                'width' => $image->width(),
                'height' => $image->height(),
                'size' => strlen(Storage::disk(self::STORAGE_DISK)->get($imagePath)),
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get URL for specific variant
     */
    public function getVariantUrl(string $basePath, string $variant = 'original'): string
    {
        if ($variant === 'original') {
            return Storage::disk(self::STORAGE_DISK)->url($basePath);
        }

        $variantPath = str_replace('.webp', "_{$variant}.webp", $basePath);
        return Storage::disk(self::STORAGE_DISK)->url($variantPath);
    }
}