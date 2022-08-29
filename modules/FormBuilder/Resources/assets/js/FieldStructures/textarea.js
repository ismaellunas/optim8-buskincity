import { defaultOption, columnFieldSizes } from '@/ComponentStructures/style-options';

export default {
    title: 'Textarea',
    componentName: 'Textarea',
    config: {
        properties: {
            name: null,
            label: 'Textarea',
            placeholder: null,
        },
        data: {
            default: null,
        },
        validation: {
            required: false,
            minLength: null,
            maxLength: null,
        },
        attributes: {
            disabled: false,
            readonly: false,
        }
    },

    type: "Textarea",
    title: "Textarea",
    column: 'is-full',
    label: "Textarea",
    name: null,
    placeholder: null,
    note: null,
    default_value: null,
    readonly: false,
    disabled: false,
    validation: {
        rules: {
            required: false,
            max: null,
            min: null,
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
                component: "ConfigCheckbox",
                label: "Disabled",
            },
            readonly: {
                component: "ConfigCheckbox",
                label: "Readonly",
            },
        },
    },
};
