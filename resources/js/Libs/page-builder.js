import { generateElementId } from './utils';

export function createColumn() {
    return {
        id: generateElementId(),
        components: [],
    };
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
    if (! (
        event.target.closest('.component-configurable')
        || event.target.closest('.page-builder-content-config')
    )) {
        contentConfigId.value = '';
    }
}
