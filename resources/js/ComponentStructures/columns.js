export default {
    title: 'Columns',
    componentName: 'Columns',
    type: 'columns',
    columns: [],
    config: {
        wrapper: {
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
