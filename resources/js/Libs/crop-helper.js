import { canvasWidth } from './defaults';

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
