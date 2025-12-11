<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\UploadedFile;

class ImageDimensionRule implements Rule
{
    private string $errorMessage = '';
    private bool $checkMin;
    private bool $checkMax;
    private bool $checkRatio;

    public function __construct(
        private string $imageType,
        bool $checkMin = true,
        bool $checkMax = true,
        bool $checkRatio = false
    ) {
        $this->checkMin = $checkMin;
        $this->checkMax = $checkMax;
        $this->checkRatio = $checkRatio;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        // Handle both UploadedFile and base64 string
        if ($value instanceof UploadedFile) {
            $dimensions = $this->getImageDimensionsFromFile($value);
        } else if (is_string($value)) {
            $dimensions = $this->getImageDimensionsFromBase64($value);
        } else {
            $this->errorMessage = 'Invalid image format.';
            return false;
        }

        if (!$dimensions) {
            $this->errorMessage = 'Unable to read image dimensions.';
            return false;
        }

        [$width, $height] = $dimensions;

        // Check minimum dimensions
        if ($this->checkMin) {
            $minDims = config("constants.min_dimensions.{$this->imageType}");
            if ($minDims) {
                if ($width < $minDims['width'] || $height < $minDims['height']) {
                    $this->errorMessage = sprintf(
                        'Image must be at least %dx%dpx. Uploaded image is %dx%dpx.',
                        $minDims['width'],
                        $minDims['height'],
                        $width,
                        $height
                    );
                    return false;
                }
            }
        }

        // Check maximum dimensions
        if ($this->checkMax) {
            $maxDims = config("constants.max_dimensions.{$this->imageType}");
            if ($maxDims) {
                if ($width > $maxDims['width'] || $height > $maxDims['height']) {
                    $this->errorMessage = sprintf(
                        'Image must not exceed %dx%dpx. Uploaded image is %dx%dpx.',
                        $maxDims['width'],
                        $maxDims['height'],
                        $width,
                        $height
                    );
                    return false;
                }
            }
        }

        // Check aspect ratio
        if ($this->checkRatio) {
            $expectedRatio = config("constants.aspect_ratios.{$this->imageType}");
            if ($expectedRatio) {
                $actualRatio = $width / $height;
                $tolerance = config('constants.aspect_ratio_tolerance', 0.1);
                $minRatio = $expectedRatio * (1 - $tolerance);
                $maxRatio = $expectedRatio * (1 + $tolerance);

                if ($actualRatio < $minRatio || $actualRatio > $maxRatio) {
                    $this->errorMessage = sprintf(
                        'Image aspect ratio must be approximately %.2f:1. Uploaded image has ratio %.2f:1.',
                        $expectedRatio,
                        $actualRatio
                    );
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return $this->errorMessage;
    }

    /**
     * Get image dimensions from an UploadedFile.
     *
     * @param UploadedFile $file
     * @return array|null [width, height] or null on failure
     */
    private function getImageDimensionsFromFile(UploadedFile $file): ?array
    {
        try {
            $imageInfo = getimagesize($file->getRealPath());
            if ($imageInfo === false) {
                return null;
            }
            return [$imageInfo[0], $imageInfo[1]];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get image dimensions from a base64 encoded string.
     *
     * @param string $base64String
     * @return array|null [width, height] or null on failure
     */
    private function getImageDimensionsFromBase64(string $base64String): ?array
    {
        try {
            // Remove data URI scheme if present
            if (strpos($base64String, 'data:image') === 0) {
                $base64String = preg_replace('/^data:image\/\w+;base64,/', '', $base64String);
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

            return [$width, $height];
        } catch (\Exception $e) {
            return null;
        }
    }
}
