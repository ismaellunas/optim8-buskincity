import { computed } from 'vue'

export function uniqueId() {
    /* @see https://gist.github.com/gordonbrander/2230317#gistcomment-1713405*/
    return (Date.now().toString(36) + Math.random().toString(36).substr(2, 5)).toUpperCase();
}

export function generateElementId() {
    return 'ID'+uniqueId().substr(0,10);
};

export function emitModelValue(emit, value, name = 'modelValue') {
    emit(`update:${name}`, value);
}

export function useModelWrapper(props, emit, name = 'modelValue') {
    return computed({
        get: () => props[name],
        set: (value) => emitModelValue(emit, value, name)
    })
}

export function isEmpty(obj) {
    let none = obj === null || obj === undefined;
    if (none) {
        return none;
    }

    if (typeof obj.size === 'number') {
        return !obj.size;
    }

    let objectType = typeof obj;

    if (typeof obj.length === 'number' && objectType !== 'function') {
        return !obj.length;
    }

    if (objectType === 'object') {
        let length = Object.keys(obj).length;
        if (typeof length === 'number') {
            return !length;
        }
    }

    return false;
}

export function isBlank(obj) {
    return isEmpty(obj) || (typeof obj === 'string' && /\S/.test(obj) === false);
}

/* @return Promise */
export function getCanvasBlob(canvas, imageType = 'image/jpeg', quality = 0.8) {
    return new Promise(function(resolve) {
        canvas.toBlob(
            (blob) => { resolve(blob); },
            imageType,
            quality
        );
    })
};

export function buildFormData(formData, data, parentKey) {
    if (
        data
        && typeof data === 'object'
        && !(data instanceof Date)
        && !(data instanceof File)
        && !(data instanceof Blob)
    ) {
        Object.keys(data).forEach(key => {
            buildFormData(formData, data[key], parentKey ? `${parentKey}[${key}]` : key);
        });
    } else {
        const value = data == null ? '' : data;

        formData.append(parentKey, value);
    }
}
