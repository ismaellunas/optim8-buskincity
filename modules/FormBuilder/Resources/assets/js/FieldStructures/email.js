import { defaultOption, columnSizes } from '@/ComponentStructures/style-options';

export default {
    type: "Email",
    title: "Email",
    column: 'is-12',
    label: "Email",
    name: null,
    placeholder: 'e.g. example@mail.com',
    note: null,
    default_value: "",
    readonly: false,
    disabled: false,
    validation: {
        rules: {
            required: false,
            max: null,
            min: null,
            email: true,
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
            placeholder: {
                component: "ConfigInput",
                label: "Placeholder",
            },
            note: {
                component: "ConfigInput",
                label: "Note",
            },
            column: {
                type: "select",
                label: "Column",
                options: defaultOption.concat(columnSizes)
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
                label: "Required",
            },
            min: {
                component: "ConfigNumber",
                label: "Min",
            },
            max: {
                component: "ConfigNumber",
                label: "Max",
            },
        },

    },
    attributes: {
        label: "Attributes",
        config: {
            disabled: {
                type: "checkbox",
                label: "Disabled",
            },
            readonly: {
                type: "checkbox",
                label: "Readonly",
            },
        },
    },
};
