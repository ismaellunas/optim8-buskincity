import {
    dimension
} from '@/ComponentStructures/global-configs.js';

export default {
    title: 'Form Builder',
    componentName: 'FormBuilder',
    module: 'FormBuilder',
    content: {},
    config: {
        form: {
            id: null
        },
        dimension: dimension.config
    }
};

export const config = {
    form: {
        label: "Form Option",
        config: {
            id: {
                component: "ConfigSelect",
                label: "Select Form",
                settings: {
                    optionsRoute: "admin.api.page-builders.form-options",
                },
            },
        }
    },
    dimension: dimension.component
};
