import { generateElementId } from './utils';
import { isEmpty, remove } from 'lodash';

export function createColumn() {
    return {
        id: generateElementId(),
        components: [],
    };
}

export function createBlock() {
    return {
        id: generateElementId(),
        type: 'columns',
        columns: [],
    }
}

export function createTrblClasses(trbl, prefix) {
    const suffix = {top: 't', right: 'r', bottom: 'b', left: 'l'};
    let classes = [];
    if (trbl) {
        for (const [key, value] of Object.entries(trbl)) {
            if (value) {
                classes.push(prefix + suffix[key] + '-' + value);
            }
        }
    }
    return classes;
};

export function createPaddingClasses(trbl) {
    return createTrblClasses(trbl, 'p');
};

export function createMarginClasses(trbl) {
    return createTrblClasses(trbl, 'm');
};

export function onPageEditorClicked(event, contentConfigId) {
    const path = event.path || (event.composedPath && event.composedPath());
    if (path) {
        const isFound = path.find((elm) => {
            if (!isEmpty(elm.classList)) {
                return (
                    elm.classList.value.includes('page-builder-content-config')
                    || elm.classList.value.includes('page-component')
                );
            }
            return false;
        })

        if (isFound === undefined && contentConfigId.value) {
            contentConfigId.value = '';
        }
    } else {
        contentConfigId.value = '';
    }
}
