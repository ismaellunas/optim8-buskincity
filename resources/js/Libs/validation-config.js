/**
 * Validation configuration for image uploads
 * This configuration is derived from Laravel config/constants.php
 */
export const imageValidation = {
    min_dimensions: {
        profile_picture: { width: 180, height: 180 },
        cover: { width: 400, height: 150 },
        gallery: { width: 600, height: 400 },
        post_thumbnail: { width: 400, height: 300 },
        logo: { width: 180, height: 180 },
        favicon: { width: 180, height: 180 },
        open_graph: { width: 1200, height: 630 },
    },
    max_dimensions: {
        profile_picture: { width: 4096, height: 4096 },
        cover: { width: 5000, height: 2000 },
        gallery: { width: 5000, height: 5000 },
        post_thumbnail: { width: 2000, height: 2000 },
        logo: { width: 2000, height: 2000 },
        favicon: { width: 512, height: 512 },
        open_graph: { width: 5000, height: 3000 },
    },
    recommended_dimensions: {
        profile_picture: { width: 600, height: 600 },
        cover: { width: 1440, height: 450 },
        gallery: { width: 1200, height: 800 },
        post_thumbnail: { width: 600, height: 400 },
        logo: { width: 300, height: 300 },
        favicon: { width: 180, height: 180 },
        open_graph: { width: 1200, height: 630 },
    },
    file_size_limits: {
        profile_picture: 3 * 1024 * 1024, // 3MB in bytes
        cover: 5 * 1024 * 1024,           // 5MB in bytes
        gallery: 10 * 1024 * 1024,        // 10MB in bytes
        general: 10 * 1024 * 1024,        // 10MB in bytes
    },
    aspect_ratios: {
        profile_picture: 1.0,      // 1:1 square
        logo: 1.0,                 // 1:1 square
        favicon: 1.0,              // 1:1 square
        cover: 3.2,                // ~820:360 (Facebook standard)
        open_graph: 1.91,          // 1200:630 (OG standard)
        qr_code_logo: 1.0,         // 1:1 square
    },
    aspect_ratio_tolerance: 0.1, // 10% tolerance
};

/**
 * Validate image dimensions against configured min/max for the image type
 * @param {number} width - Image width in pixels
 * @param {number} height - Image height in pixels
 * @param {string} imageType - Type of image (profile_picture, cover, gallery, etc.)
 * @returns {Object} {valid: boolean, errors: string[], warnings: string[]}
 */
export function validateImageDimensions(width, height, imageType = 'general') {
    const errors = [];
    const warnings = [];

    // Get configured dimensions
    const minDims = imageValidation.min_dimensions[imageType];
    const maxDims = imageValidation.max_dimensions[imageType];
    const recommendedDims = imageValidation.recommended_dimensions[imageType];

    // Check minimum dimensions
    if (minDims) {
        if (width < minDims.width || height < minDims.height) {
            errors.push(
                `Image must be at least ${minDims.width}x${minDims.height}px. ` +
                `Current size is ${width}x${height}px.`
            );
        }
    }

    // Check maximum dimensions
    if (maxDims) {
        if (width > maxDims.width || height > maxDims.height) {
            errors.push(
                `Image must not exceed ${maxDims.width}x${maxDims.height}px. ` +
                `Current size is ${width}x${height}px.`
            );
        }
    }

    // Warning for non-recommended dimensions
    if (recommendedDims) {
        if (width !== recommendedDims.width || height !== recommendedDims.height) {
            warnings.push(
                `Recommended size is ${recommendedDims.width}x${recommendedDims.height}px for best quality.`
            );
        }
    }

    return {
        valid: errors.length === 0,
        errors,
        warnings,
    };
}

/**
 * Validate aspect ratio of an image
 * @param {number} width - Image width in pixels
 * @param {number} height - Image height in pixels
 * @param {string} imageType - Type of image
 * @returns {Object} {valid: boolean, message: string|null, ratio: number}
 */
export function validateAspectRatio(width, height, imageType) {
    const expectedRatio = imageValidation.aspect_ratios[imageType];

    if (!expectedRatio) {
        return {
            valid: true,
            message: null,
            ratio: width / height,
        };
    }

    const actualRatio = width / height;
    const tolerance = imageValidation.aspect_ratio_tolerance;
    const minRatio = expectedRatio * (1 - tolerance);
    const maxRatio = expectedRatio * (1 + tolerance);

    const isValid = actualRatio >= minRatio && actualRatio <= maxRatio;

    return {
        valid: isValid,
        message: isValid ? null : 
            `Image aspect ratio should be approximately ${expectedRatio.toFixed(2)}:1. ` +
            `Current ratio is ${actualRatio.toFixed(2)}:1.`,
        ratio: actualRatio,
    };
}

/**
 * Format file size in bytes to human-readable format
 * @param {number} bytes - File size in bytes
 * @returns {string} Formatted file size
 */
export function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';

    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

/**
 * Validate file size against configured limit for the image type
 * @param {number} fileSize - File size in bytes
 * @param {string} imageType - Type of image
 * @returns {Object} {valid: boolean, message: string|null, limit: number}
 */
export function validateFileSize(fileSize, imageType = 'general') {
    const limit = imageValidation.file_size_limits[imageType] || 
                  imageValidation.file_size_limits.general;

    const isValid = fileSize <= limit;

    return {
        valid: isValid,
        message: isValid ? null :
            `File size (${formatFileSize(fileSize)}) exceeds the limit of ${formatFileSize(limit)}.`,
        limit,
    };
}

/**
 * Get readable validation requirements for display
 * @param {string} imageType - Type of image
 * @returns {Object} Requirements object with formatted strings
 */
export function getValidationRequirements(imageType) {
    const minDims = imageValidation.min_dimensions[imageType];
    const maxDims = imageValidation.max_dimensions[imageType];
    const recommendedDims = imageValidation.recommended_dimensions[imageType];
    const fileLimit = imageValidation.file_size_limits[imageType] || 
                      imageValidation.file_size_limits.general;
    const aspectRatio = imageValidation.aspect_ratios[imageType];

    return {
        minDimensions: minDims ? `${minDims.width} x ${minDims.height}px` : null,
        maxDimensions: maxDims ? `${maxDims.width} x ${maxDims.height}px` : null,
        recommendedDimensions: recommendedDims ? 
            `${recommendedDims.width} x ${recommendedDims.height}px` : null,
        maxFileSize: formatFileSize(fileLimit),
        aspectRatio: aspectRatio ? `${aspectRatio}:1` : null,
    };
}
