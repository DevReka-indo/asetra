<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

trait HandlesImageUploads
{
    /**
     * Compress and optionally resize an image, then save it to the public storage disk.
     *
     * @param UploadedFile|string $file UploadedFile instance or absolute path to a file
     * @param string $folder Target folder under public storage
     * @param string|null $filename Custom filename, auto-generated if null
     * @param int $maxDimension Maximum width or height of the image (default 1200px)
     * @param int $quality Compression quality 0-100 (default 75)
     * @return string|null Relative path from public disk, or null on failure
     */
    public function compressAndStore($file, string $folder, ?string $filename = null, int $maxDimension = 1200, int $quality = 75): ?string
    {
        try {
            $filePath = is_string($file) ? $file : $file->getRealPath();
            
            if (!$filename) {
                $filename = time() . '_' . uniqid() . '.jpg'; // Convert to jpg for optimal size
            } else {
                // Ensure extension is jpg/jpeg for consistency if we compress to jpeg
                $pathInfo = pathinfo($filename);
                $filename = $pathInfo['filename'] . '.jpg';
            }

            // Get image info
            $imageInfo = @getimagesize($filePath);
            if (!$imageInfo) {
                // Not a valid image or getimagesize failed, store as is
                if (is_string($file)) {
                    $content = file_get_contents($file);
                    Storage::disk('public')->put("{$folder}/{$filename}", $content);
                } else {
                    $file->storeAs($folder, $filename, 'public');
                }
                return "{$folder}/{$filename}";
            }

            list($width, $height, $type) = $imageInfo;

            // Load image depending on type
            switch ($type) {
                case IMAGETYPE_JPEG:
                    $srcImage = @imagecreatefromjpeg($filePath);
                    break;
                case IMAGETYPE_PNG:
                    $srcImage = @imagecreatefrompng($filePath);
                    break;
                case IMAGETYPE_WEBP:
                    $srcImage = @imagecreatefromwebp($filePath);
                    break;
                default:
                    // Unsupported GD format, store as is
                    if (is_string($file)) {
                        $content = file_get_contents($file);
                        Storage::disk('public')->put("{$folder}/{$filename}", $content);
                    } else {
                        $file->storeAs($folder, $filename, 'public');
                    }
                    return "{$folder}/{$filename}";
            }

            if (!$srcImage) {
                // Failed to create image resource, fallback to standard store
                if (is_string($file)) {
                    $content = file_get_contents($file);
                    Storage::disk('public')->put("{$folder}/{$filename}", $content);
                } else {
                    $file->storeAs($folder, $filename, 'public');
                }
                return "{$folder}/{$filename}";
            }

            // Calculate new dimensions (Resize keeping aspect ratio)
            $newWidth = $width;
            $newHeight = $height;

            if ($width > $maxDimension || $height > $maxDimension) {
                if ($width > $height) {
                    $newWidth = $maxDimension;
                    $newHeight = (int) (($height / $width) * $maxDimension);
                } else {
                    $newHeight = $maxDimension;
                    $newWidth = (int) (($width / $height) * $maxDimension);
                }
            }

            // Create target true color image
            $dstImage = imagecreatetruecolor($newWidth, $newHeight);

            // Resample image
            imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            // Use output buffering to capture JPEG data stream
            ob_start();
            imagejpeg($dstImage, null, $quality);
            $imageData = ob_get_clean();

            // Save to public storage
            Storage::disk('public')->put("{$folder}/{$filename}", $imageData);

            // Clean up memory
            imagedestroy($srcImage);
            imagedestroy($dstImage);

            return "{$folder}/{$filename}";
        } catch (\Exception $e) {
            Log::error('Image Compression Error: ' . $e->getMessage());
            // Fallback: save without compression
            try {
                if (is_string($file)) {
                    $fallbackFilename = $filename ?? basename($file);
                    $content = file_get_contents($file);
                    Storage::disk('public')->put("{$folder}/{$fallbackFilename}", $content);
                    return "{$folder}/{$fallbackFilename}";
                } else {
                    $fallbackFilename = $filename ?? ($file->getClientOriginalName());
                    $file->storeAs($folder, $fallbackFilename, 'public');
                    return "{$folder}/{$fallbackFilename}";
                }
            } catch (\Exception $fallbackException) {
                Log::error('Image Compression Fallback Error: ' . $fallbackException->getMessage());
                return null;
            }
        }
    }
}
