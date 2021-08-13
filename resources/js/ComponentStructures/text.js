import { contentSizes, defaultOption, alignments } from './style-options';

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
        },
        wrapper: {
            margin: {
                top: null,
                right: null,
                bottom: null,
                left: null,
            },
            padding: {
                top: null,
                right: null,
                bottom: null,
                left: null,
            },
        }
    }
};

export const config = {
    text: {
        label: "Text",
        config: {
            size: {
                type: "select",
                label: "Size",
                options: defaultOption.concat(contentSizes)
            },
            alignment: {
                type: "select",
                label: "Alignment",
                options: defaultOption.concat(alignments)
            },
        }
    },
    wrapper: {
        label: "Wrapper",
        config: {
            margin: {
                component: "TRBL",
                label: "Margin",
            },
            padding: {
                component: "TRBL",
                label: "Padding",
            }
        }
    }
};
