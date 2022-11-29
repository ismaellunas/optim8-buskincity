import {
    backgroundColors,
    defaultOption,
    roundedSizes,
} from './style-options';

import {
    dimension
} from './global-configs.js';

export default {
    title: 'Columns',
    componentName: 'Columns',
    type: 'columns',
    columns: [],
    config: {
        wrapper: {
            customId: null,
            isFullwidth: false,
            backgroundColor: null,
            backgroundImage: null,
            rounded: null,
        },
        section: {
            isIncluded: false,
            size: null,
        },
        columns: {
            isCentered: false,
            column: [
                {
                    size: "auto",
                }
            ],
        },
        dimension: dimension.config,
    },
};

export const config = {
    wrapper: {
        label: "Wrapper",
        isOpen: false,
        config: {
            customId: {
                component: "ConfigInput",
                label: "Custom ID",
                settings: {
                    placeholder: "custom-id",
                    note: "Custom ID must be unique.",
                },
            },
            isFullwidth: {
                component: "ConfigSelect",
                label: "Fullwidth",
                settings: {
                    options: [
                        { value: false, name: "No"},
                        { value: true, name: "Yes"},
                    ],
                },
            },
            backgroundColor: {
                component: "ConfigSelect",
                label: "Background Color",
                settings: {
                    options: defaultOption.concat(backgroundColors),
                },
            },
            backgroundImage: {
                component: "ConfigImageBrowser",
                label: "Background Image",
            },
            rounded: {
                component: "ConfigSelect",
                label: "Rounded Size",
                settings: {
                    options: defaultOption.concat(roundedSizes),
                },
            },
        }
    },
    section: {
        label: "Section",
        component: "ConfigRowSection",
    },
    columns: {
        label: "Columns",
        component: "ConfigColumns",
    },
    dimension: dimension.component,
};
