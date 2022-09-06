export default {
    title: 'Form Builder',
    componentName: 'FormBuilder',
    content: {},
    config: {
        form: {
            id: null
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
    form: {
        label: "Form Option",
        config: {
            id: {
                component: "Select",
                label: "Select Form",
                settings: {
                    optionsRoute: "admin.api.page-builders.form-options",
                },
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
