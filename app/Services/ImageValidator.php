<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

class ImageValidator
{
    /**
     * Validate image dimensions against configured min/max for the image type.
     *
     * @param UploadedFile|string $file
     * @param string $imageType
     * @param bool $checkMin
     * @param bool $checkMax
     * @return array ['valid' => bool, 'errors' => array, 'dimensions' => array]
     */
    public static function validateDimensions(
        UploadedFile|string $file,
        string $imageType,
        bool $checkMin = true,
        bool $checkMax = true
    ): array {
        $info = self::getImageInfo($file);

        if (!$info) {
            return [
                'valid' => false,
                'errors' => ['Unable to read image information.'],
                'dimensions' => null,
            ];
        }

        $errors = [];
        $width = $info['width'];
        $height = $info['height'];

        // Check minimum dimensions
        if ($checkMin) {
            $minDims = config("constants.min_dimensions.{$imageType}");
            if ($minDims) {
                if ($width < $minDims['width'] || $height < $minDims['height']) {
                    $errors[] = sprintf(
                        'Image must be at least %dx%dpx. Current size is %dx%dpx.',
                        $minDims['width'],
                        $minDims['height'],
                        $width,
                        $height
                    );
                }
            }
        }

        // Check maximum dimensions
        if ($checkMax) {
            $maxDims = config("constants.max_dimensions.{$imageType}");
            if ($maxDims) {
                if ($width > $maxDims['width'] || $height > $maxDims['height']) {
                    $errors[] = sprintf(
                        'Image must not exceed %dx%dpx. Current size is %dx%dpx.',
                        $maxDims['width'],
                        $maxDims['height'],
                        $width,
                        $height
                    );
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'dimensions' => [
                'width' => $width,
                'height' => $height,
            ],
        ];
    }

    /**
     * Validate aspect ratio of an image against configured ratio for the image type.
     *
     * @param int $width
     * @param int $height
     * @param string $imageType
     * @return array ['valid' => bool, 'message' => string|null, 'ratio' => float]
     */
    public static function validateAspectRatio(
        int $width,
        int $height,
        string $imageType
    ): array {
        $expectedRatio = config("constants.aspect_ratios.{$imageType}");

        if (!$expectedRatio) {
            return [
                'valid' => true,
                'message' => null,
                'ratio' => $width / $height,
            ];
        }

        $actualRatio = $width / $height;
        $tolerance = config('constants.aspect_ratio_tolerance', 0.1);
        $minRatio = $expectedRatio * (1 - $tolerance);
        $maxRatio = $expectedRatio * (1 + $tolerance);

        $isValid = $actualRatio >= $minRatio && $actualRatio <= $maxRatio;

        return [
            'valid' => $isValid,
            'message' => $isValid ? null : sprintf(
                'Image aspect ratio should be approximately %.2f:1. Current ratio is %.2f:1.',
                $expectedRatio,
                $actualRatio
            ),
            'ratio' => $actualRatio,
        ];
    }

    /**
     * Get comprehensive image information.
     *
     * @param UploadedFile|string $file
     * @return array|null ['width' => int, 'height' => int, 'fileSize' => int, 'mimeType' => string]
     */
    public static function getImageInfo(UploadedFile|string $file): ?array
    {
        try {
            if ($file instanceof UploadedFile) {
                return self::getInfoFromUploadedFile($file);
            } else if (is_string($file)) {
                return self::getInfoFromBase64($file);
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    /**
     * Get image info from an UploadedFile.
     *
     * @param UploadedFile $file
     * @return array|null
     */
    private static function getInfoFromUploadedFile(UploadedFile $file): ?array
    {
        $imageInfo = getimagesize($file->getRealPath());
        if ($imageInfo === false) {
            return null;
        }

        return [
            'width' => $imageInfo[0],
            'height' => $imageInfo[1],
            'fileSize' => $file->getSize(),
            'mimeType' => $file->getMimeType(),
        ];
    }

    /**
     * Get image info from a base64 encoded string.
     *
     * @param string $base64String
     * @return array|null
     */
    private static function getInfoFromBase64(string $base64String): ?array
    {
        // Remove data URI scheme if present
        if (strpos($base64String, 'data:image') === 0) {
            preg_match('/^data:image\/(\w+);base64,/', $base64String, $matches);
            $mimeType = isset($matches[1]) ? "image/{$matches[1]}" : 'image/jpeg';
            $base64String = preg_replace('/^data:image\/\w+;base64,/', '', $base64String);
        } else {
            $mimeType = 'image/jpeg';
        }

        $imageData = base64_decode($base64String);
        if ($imageData === false) {
            return null;
        }

        $image = imagecreatefromstring($imageData);
        if ($image === false) {
            return null;
        }

        $width = imagesx($image);
        $height = imagesy($image);
        imagedestroy($image);

        return [
            'width' => $width,
            'height' => $height,
            'fileSize' => strlen($imageData),
            'mimeType' => $mimeType,
        ];
    }

    /**
     * Get recommended dimensions for an image type.
     *
     * @param string $imageType
     * @return array|null ['width' => int, 'height' => int]
     */
    public static function getRecommendedDimensions(string $imageType): ?array
    {
        return config("constants.dimensions.{$imageType}");
    }

    /**
     * Get minimum dimensions for an image type.
     *
     * @param string $imageType
     * @return array|null ['width' => int, 'height' => int]
     */
    public static function getMinDimensions(string $imageType): ?array
    {
        return config("constants.min_dimensions.{$imageType}");
    }

    /**
     * Get maximum dimensions for an image type.
     *
     * @param string $imageType
     * @return array|null ['width' => int, 'height' => int]
     */
    public static function getMaxDimensions(string $imageType): ?array
    {
        return config("constants.max_dimensions.{$imageType}");
    }
}
