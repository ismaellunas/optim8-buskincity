import { defaultOption, columnFieldSizes } from '@/ComponentStructures/style-options';

export default {
    type: "Number",
    title: "Number",
    column: 'is-full',
    label: "Number",
    name: "number",
    placeholder: null,
    note: null,
    default_value: null,
    readonly: false,
    disabled: false,
    validation: {
        rules: {
            required: false,
            min: null,
            max: null,
            min_digits: null,
            max_digits: null,
        },
        message: []
    },
    visibility: [],
    translated: false,
};

export const config = {
    properties: {
        label: "Properties",
        config: {
            label: {
                component: "ConfigInput",
                label: "Label",
            },
            name: {
                component: "ConfigAutoGenerateKey",
                label: "Name",
                settings: {
                    generateBy: 'label'
                },
            },
            placeholder: {
                component: "ConfigInput",
                label: "Placeholder",
            },
            note: {
                component: "ConfigInput",
                label: "Note",
            },
            column: {
                component: "ConfigSelect",
                label: "Column",
                settings: {
                    options: defaultOption.concat(columnFieldSizes),
                }
            },
        }
    },
    data: {
        label: "Data",
        config: {
            default_value: {
                component: "ConfigInput",
                label: "Default Value",
            }
        },
    },
    validation: {
        label: "Validation",
        config: {
            required: {
                component: "ConfigCheckbox",
                label: "Is Required?",
            },
            min: {
                component: "ConfigNumber",
                label: "Minimal Value",
            },
            max: {
                component: "ConfigNumber",
                label: "Maximal Value",
            },
            'min_digits': {
                component: "ConfigNumber",
                label: "Minimal Length of Value",
            },
            'max_digits': {
                component: "ConfigNumber",
                label: "Maximal Length of Value",
            },
        },

    },
    attributes: {
        label: "Attributes",
        config: {
            disabled: {
                component: "ConfigCheckbox",
                label: "Is Disabled?",
            },
            readonly: {
                component: "ConfigCheckbox",
                label: "Is Readonly?",
            },
        },
    },
};
