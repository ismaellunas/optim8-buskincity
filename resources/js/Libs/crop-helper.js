import { canvasWidth } from './defaults';
import { validateImageDimensions, validateFileSize, formatFileSize } from './validation-config';

export function getCanvas(cropper, width) {
    const canvasOptions = {};

    if (! width) {
        canvasOptions.width = width;
    }

    const canvas = cropper.getCroppedCanvas(canvasOptions);

    if (
        (!width && width > canvasWidth)
        || canvas.getAttribute('width') > canvasWidth
    ) {
        return cropper.getCroppedCanvas({ maxWidth: canvasWidth });
    }

    return canvas;
};

export function getBlobFromCanvas(canvas, imageType = 'image/jpeg', quality = 0.8) {
    return new Promise(function(resolve) {
        canvas.toBlob(
            (blob) => { resolve(blob); },
            imageType,
            quality
        );
    })
};

export function getBlob(cropper, imageType = 'image/jpeg') {
    return new Promise((resolve) => {
        const canvas = getCanvas(cropper);

        getBlobFromCanvas(canvas, imageType).then(blob => {
            const sizeMB = blob.size/1000000;

            if (sizeMB > 2) {
                getBlobFromCanvas(canvas, 'image/jpeg').then(blob => {
                    resolve(blob)
                })
            } else {
                resolve(blob);
            }
        });
    });
};

/**
 * Get current canvas dimensions from cropper
 * @param {Object} cropper - VueCropper instance
 * @returns {Object} {width: number, height: number}
 */
export function getCanvasDimensions(cropper) {
    const canvas = cropper.getCroppedCanvas();
    return {
        width: canvas.width,
        height: canvas.height,
    };
};

/**
 * Estimate the file size of a cropped image
 * @param {Object} cropper - VueCropper instance
 * @param {string} imageType - MIME type of the image
 * @returns {Promise<number>} Estimated file size in bytes
 */
export async function estimateCroppedFileSize(cropper, imageType = 'image/jpeg') {
    const blob = await getBlob(cropper, imageType);
    return blob.size;
};

/**
 * Validate cropped image dimensions and size
 * @param {Object} cropper - VueCropper instance
 * @param {string} imageType - Type of image (profile_picture, cover, etc.)
 * @returns {Promise<Object>} Validation result with errors and warnings
 */
export async function validateCroppedImage(cropper, imageType = 'general') {
    const dims = getCanvasDimensions(cropper);
    const fileSize = await estimateCroppedFileSize(cropper);

    const dimValidation = validateImageDimensions(dims.width, dims.height, imageType);
    const sizeValidation = validateFileSize(fileSize, imageType);

    const errors = [...dimValidation.errors];
    const warnings = [...dimValidation.warnings];

    if (!sizeValidation.valid) {
        errors.push(sizeValidation.message);
    }

    return {
        valid: errors.length === 0,
        errors,
        warnings,
        dimensions: dims,
        fileSize,
        fileSizeFormatted: formatFileSize(fileSize),
    };
};

// Re-export validation functions for convenience
export { validateImageDimensions, validateFileSize, formatFileSize };
