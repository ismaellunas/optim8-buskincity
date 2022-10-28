import {
    colors,
    contentPositions,
    contentSizes,
    defaultOption,
    otherColors,
} from './style-options';

import {
    dimension
} from './global-configs.js';

export default {
    title: 'Button',
    componentName: 'Button',
    content: {
        button: {
            text: "Button",
            icon: null,
        },
    },
    config: {
        button: {
            link: null,
            target: null,
            color: null,
            isLight: false,
            size: null,
            width: null,
            style: null,
            position: null,
            iconPosition: 'left',
        },
        dimension: dimension.config
    }
};

export const config = {
    button: {
        label: "Button",
        config: {
            link: {
                component: "ConfigInput",
                label: "Link",
                settings: {
                    note: "E.g: https://www.google.com",
                },
            },
            target: {
                component: "ConfigSelect",
                label: "Target",
                settings: {
                    options: defaultOption.concat(
                        [
                            { value: "_blank", name: "New window"},
                            { value: "download", name: "Download"},
                        ]
                    ),
                },
            },
            color: {
                component: "ConfigSelect",
                label: "Color",
                settings: {
                    options: defaultOption.concat(otherColors, colors),
                },
            },
            isLight: {
                component: "checkbox",
                label: "Light Button?",
            },
            size: {
                component: "ConfigSelect",
                label: "Size",
                settings: {
                    options: defaultOption.concat(contentSizes),
                },
            },
            width: {
                component: "ConfigSelect",
                label: "Width",
                settings: {
                    options: defaultOption.concat(
                        [
                            { value: "is-fullwidth", name: "Fullwidth"},
                        ]
                    ),
                },
            },
            style: {
                component: "ConfigSelect",
                label: "Style",
                settings: {
                    options: defaultOption.concat(
                        [
                            { value: "is-rounded", name: "Rounded"},
                            { value: "is-outlined", name: "Outlined"},
                            { value: "is-inverted", name: "Inverted"},
                            { value: "is-rounded is-outlined", name: "Rounded & Outlined"},
                        ]
                    ),
                },
            },
            position: {
                component: "ConfigSelect",
                label: "Button Position",
                settings: {
                    options: defaultOption.concat(contentPositions),
                },
            },
            iconPosition: {
                component: "ConfigSelect",
                label: "Icon Position",
                settings: {
                    options: defaultOption.concat(
                        [
                            { value: "left", name: "Left"},
                            { value: "right", name: "Right"},
                        ]
                    ),
                },
            },
        }
    },
    dimension: dimension.component
};
