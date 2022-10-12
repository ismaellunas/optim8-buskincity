import {
    backgroundColors,
    defaultOption,
} from './style-options';

export default {
    title: 'Columns',
    componentName: 'Columns',
    type: 'columns',
    columns: [],
    config: {
        wrapper: {
            isFullwidth: false,
            backgroundColor: null,
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
        },
        section: {
            isIncluded: false,
            size: null,
        }
    },
};

export const config = {
    wrapper: {
        label: "Wrapper",
        isOpen: false,
        config: {
            isFullwidth: {
                type: "select",
                label: "Fullwidth",
                options: [
                    { value: false, name: "No"},
                    { value: true, name: "Yes"},
                ],
            },
            backgroundColor: {
                type: "select",
                label: "Background Color",
                options: defaultOption.concat(backgroundColors),
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
    },
    section: {
        label: "Section",
        component: "ConfigRowSection",
    }
};
