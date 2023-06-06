import { computed, onMounted, onUnmounted, ref } from 'vue';
import { inRange} from 'lodash';
import mime from 'mime';

export const regexFileName = "a-z0-9\-";
export const regexSlug = regexFileName;

export function uniqueId() {
    /* @see https://gist.github.com/gordonbrander/2230317#gistcomment-1713405*/
    return (Date.now().toString(36) + Math.random().toString(36).substr(2, 5)).toUpperCase();
}

export function generateElementId() {
    return 'ID'+uniqueId().substr(0,10);
};

export function useModelWrapper(props, emit, name = 'modelValue') {
    return computed({
        get: () => props[name],
        set: (value) => emit(`update:${name}`, value)
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
};

// @see https://stackoverflow.com/questions/1714786/query-string-encoding-of-a-javascript-object
export function serialize(obj, prefix) {
    var queryString = [];
    var p;

    for (p in obj) {
        if (obj.hasOwnProperty(p)) {
            var k = prefix ? prefix + "[" + p + "]" : p;
            var v = obj[p];

            queryString.push(
                (v !== null && typeof v === "object")
                ? serialize(v, k)
                : encodeURIComponent(k) + "=" + encodeURIComponent(v)
            );
        }
    }
    return queryString.join("&");
};

export function convertToSlug(text) {
    let result = text
        .toLowerCase()
        .replace(/ /g,'-')
        .replace(/[-]+/g, '-')
        .replace(/[^\w-]+/g,'');

    if (result && result.slice(-1) === '-') {
        return result.slice(0, -1);
    }

    return result;
}

export function convertToKey(text) {
    let result = text
        .toLowerCase()
        .replace(/ /g,'_')
        .replace(/[-]+/g, '_')
        .replace(/[^\w-]+/g,'');

    if (result && result.slice(-1) === '_') {
        return result.slice(0, -1);
    }

    return result;
}

export function getResourceFromDataObject(dataObject, keyName) {
    const resource = [];

    JSON.stringify(dataObject, (key, value) => {
        if (key === keyName) {
            resource.push(value);
        }

        return value;
    });

    return resource;
}

export function getPhoneCountries(url = null) {
    if (url === null ) {
        url = route('admin.api.options.phone-countries');
    }

    return axios.get(url)
        .then((response) => {return response.data; })
        .catch((error) => { throw error; });
}

export function statusCodeColor(code) {
    if (inRange(code, 0, 300)) {
        return "is-light";
    } else if (inRange(code, 300, 400)) {
        return "is-info";
    } else if (inRange(code, 400, 500)) {
        return "is-warning";
    } else if (inRange(code, 500, 600)) {
        return "is-danger";
    }

    return "";
}

export function extensionToMimes(extensions) {
    const mimes = [];

    extensions.forEach(function (extension) {
        mimes.push(mime.getType(extension));
    });

    return mimes.filter(Boolean);
}