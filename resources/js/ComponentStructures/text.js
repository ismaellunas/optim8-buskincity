import {
    contentSizes,
    defaultOption,
    alignments,
    textColors
} from './style-options';

import {
    dimension
} from './global-configs.js';

export default {
    title: 'Text',
    componentName: 'Text',
    content: {
        html: null,
    },
    config: {
        text: {
            size: null,
            alignment: null,
            color: null,
        },
        dimension: dimension.config
    }
};

export const config = {
    text: {
        label: "Text",
        config: {
            size: {
                component: "ConfigSelect",
                label: "Size",
                settings: {
                    options: defaultOption.concat(contentSizes)
                },
            },
            alignment: {
                component: "ConfigSelect",
                label: "Alignment",
                settings: {
                    options: defaultOption.concat(alignments)
                },
            },
            color: {
                component: "ConfigSelect",
                label: "Color",
                settings: {
                    options: defaultOption.concat(textColors),
                },
            },
        }
    },
    dimension: dimension.component
};
