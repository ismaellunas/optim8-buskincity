import {
    defaultOption,
    textColors,

} from './style-options';

import {
    dimension
} from './global-configs.js';

export default {
    title: 'Icon',
    componentName: 'Icon',
    content: {},
    config: {
        icon: {
            class: null,
            alignment: null,
            color: null,
        },
        style: {
            size: null,
        },
        dimension: dimension.config
    }
};

export const config = {
    icon: {
        label: "Icon",
        config: {
            class: {
                label: "Class",
                component: "ConfigInputIcon",
            },
            alignment: {
                component: "ConfigSelect",
                label: "Alignment",
                settings: {
                    options: defaultOption.concat(
                        [
                            { value: "has-text-centered", name: "Centered"},
                            { value: "has-text-left", name: "Left"},
                            { value: "has-text-right", name: "Right"},
                        ]
                    ),
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
    style: {
        label: "Style",
        config: {
            size: {
                label: "Size",
                component: "ConfigNumberAddons",
                settings: {
                    addons: 'px',
                },
            },
        }
    },
    dimension: dimension.component
};
