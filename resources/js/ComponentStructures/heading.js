import { alignments, defaultOption, textColors } from './style-options';

import {
    dimension
} from './global-configs.js';

export default {
    title: "Heading",
    componentName: "Heading",
    content: {
        heading: {
            html: "",
        },
    },
    config: {
        heading: {
            tag: "h1",
            type: "title",
            alignment: null,
            color: null,
        },
        dimension: dimension.config
    }
};

export const config = {
    heading: {
        label: "Heading",
        config: {
            tag: {
                component: "ConfigSelect",
                label: "Heading",
                settings: {
                    options: [
                        { value: "h1", name: "H1" },
                        { value: "h2", name: "H2" },
                        { value: "h3", name: "H3" },
                        { value: "h4", name: "H4" },
                        { value: "h5", name: "H5" },
                        { value: "h6", name: "H6" },
                    ],
                },
            },
            type: {
                component: "ConfigSelect",
                label: "Type",
                settings: {
                    options: [
                        { value: "title", name: "Title"},
                        { value: "subtitle", name: "Subtitle"},
                    ],
                },
            },
            alignment: {
                component: "ConfigSelect",
                label: "Alignment",
                settings: {
                    options: defaultOption.concat(alignments),
                },
            },
            color: {
                component: "ConfigSelect",
                label: "Text Color",
                settings: {
                    options: defaultOption.concat(textColors),
                },
            },
        },
    },
    dimension: dimension.component
};
