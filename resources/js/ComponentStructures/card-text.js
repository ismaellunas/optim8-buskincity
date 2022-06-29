import { contentSizes, defaultOption } from './style-options';

export default {
    title: 'Card Text',
    componentName: 'CardText',
    content: {
        cardContent: {
            content: {
                html: "",
            },
            media: {},
        }
    },
    config: {
        content: {
            size: null,
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
}

export const config = {
    content: {
        label: "Content",
        config: {
            size: {
                type: "select",
                label: "Size",
                options: defaultOption.concat(contentSizes),
            }
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
