export const dimension = {
    config: {
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
        }
    },
    component: {
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

export default {
    dimension,
}