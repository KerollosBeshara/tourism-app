<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Format;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Exception;

class ImageService
{
    private const STORAGE_DISK = 's3';
    private const MAX_FILE_SIZE = 10485760; // 10MB
    
    // Centralized configuration for variants
    private const VARIANTS = [
        'thumbnail' => [300, 300],
        'medium'    => [800, 800]
    ];

    private ImageManager $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    public function validate(UploadedFile $file): bool
    {
        if (!$file->isValid()) {
            throw new InvalidArgumentException('Invalid file upload.');
        }

        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new InvalidArgumentException('File size exceeds 10MB limit.');
        }

        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file->getMimeType(), $allowed)) {
            throw new InvalidArgumentException('Invalid image format.');
        }

        return true;
    }

    public function uploadAndOptimize(
        string $targetDirectory, 
        UploadedFile $file, 
        int $width = 1200, 
        int $height = 800,
        bool $createVariants = true
    ): array {
        $this->validate($file);

        $sourceImage = $this->imageManager->decodePath($file->getRealPath());
        $filename = Str::random(12) . '.webp';
        $basePath = "{$targetDirectory}/{$filename}";

        // 1. Process and upload Original
        $original = $sourceImage->scale($width, $height)->encodeUsingFormat(Format::WEBP);
        
        if (!Storage::disk(self::STORAGE_DISK)->put($basePath, (string) $original)) {
            throw new Exception("Failed to upload original image to S3.");
        }

        $result = [
            'original' => [
                's3_path'   => $basePath,
                'file_size' => strlen((string) $original),
            ],
        ];

        // 2. Process Variants
        if ($createVariants) {
            foreach (self::VARIANTS as $key => [$vWidth, $vHeight]) {
                $variant = $sourceImage->cover($vWidth, $vHeight)->encodeUsingFormat(Format::WEBP);
                $variantPath = "{$targetDirectory}/" . str_replace('.webp', "_{$key}.webp", $filename);
                
                Storage::disk(self::STORAGE_DISK)->put($variantPath, (string) $variant);
                
                $result[$key] = [
                    's3_path'   => $variantPath,
                    'file_size' => strlen((string) $variant),
                ];
            }
        }

        return $result;
    }

    public function deleteFromS3(string $path): bool
    {
        $disk = Storage::disk(self::STORAGE_DISK);
        
        // Collect main path + all defined variants
        $pathsToDelete = [$path];
        foreach (array_keys(self::VARIANTS) as $key) {
            $pathsToDelete[] = str_replace('.webp', "_{$key}.webp", $path);
        }

        // Perform batch delete for better performance
        return $disk->delete($pathsToDelete);
    }

    public function getImageMetadata(string $imagePath): array
    {
        try {
            $content = Storage::disk(self::STORAGE_DISK)->get($imagePath);
            if (!$content) return [];
            
            $image = $this->imageManager->decodeBinary($content);
            return [
                'width'  => $image->width(),
                'height' => $image->height(),
                'size'   => strlen($content),
            ];
        } catch (Exception $e) {
            return [];
        }
    }
}