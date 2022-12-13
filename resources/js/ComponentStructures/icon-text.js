import {
    contentSizes,
    defaultOption,
    alignments,
    textColors,
    textWeights
} from './style-options';

import {
    dimension
} from './global-configs.js';

export default {
    title: 'Icon Text',
    componentName: 'IconText',
    content: {
        text: "",
    },
    config: {
        text: {
            size: null,
            alignment: null,
            color: null,
            weight: null
        },
        icon: {
            class: null,
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
            weight: {
                component: "ConfigSelect",
                label: "Weight",
                settings: {
                    options: defaultOption.concat(textWeights),
                },
            },
        }
    },
    icon: {
        label: "Icon",
        config: {
            class: {
                label: "Class",
                component: "ConfigInputIcon",
            },
        }
    },
    dimension: dimension.component
};
