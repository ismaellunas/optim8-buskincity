export default {
    title: 'Columns',
    componentName: 'Columns',
    type: 'columns',
    columns: [],
    config: {
        wrapper: {
            isFullwidth: false,
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
