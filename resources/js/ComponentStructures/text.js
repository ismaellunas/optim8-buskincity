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
        dimension: {
            'style.padding': {
                top: null,
                right: null,
                bottom: null,
                left: null,
                unit: 'px',
            },
            'style.margin': {
                top: null,
                right: null,
                bottom: null,
                left: null,
                unit: 'px',
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
    dimension: {
        label: "Dimension",
        isOpen: false,
        config: {
            'style.margin': {
                component: "TRBLInput",
                label: "Margin",
            },
            'style.padding': {
                component: "TRBLInput",
                label: "Padding",
            },
        }
    }
};
