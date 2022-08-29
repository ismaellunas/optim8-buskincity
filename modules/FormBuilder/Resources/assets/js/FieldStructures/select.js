import { defaultOption, columnFieldSizes } from '@/ComponentStructures/style-options';

export default {
    type: "Select",
    title: "Select",
    column: 'is-full',
    label: "Select",
    name: 'select',
    placeholder: null,
    note: null,
    default_value: null,
    readonly: false,
    disabled: false,
    options: [
        {
            id: 'id',
            value: 'value'
        }
    ],
    validation: {
        rules: {
            required: false,
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
                component: "ConfigInput",
                label: "Name",
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
            },
            options: {
                component: "AddOption",
                label: "Options",
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
