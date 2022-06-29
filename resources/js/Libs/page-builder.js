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

export function createDimensionStyles(dimension, prefix) {
    const styles = {};

    if (dimension && dimension["style." + prefix]) {
        const dimensionStyle = dimension["style." + prefix];

        for (const [key, style] of Object.entries(dimensionStyle)) {
            if (! (dimensionStyle[key] == null || dimensionStyle[key] == "")) {
                styles[prefix+'-'+key] = dimensionStyle[key]+'px !important';
            }
        }
    }

    return styles;
}

export function createMarginStyles(dimension) {
    return createDimensionStyles(dimension, 'margin');
}

export function createPaddingStyles(dimension) {
    return createDimensionStyles(dimension, 'padding');
}
