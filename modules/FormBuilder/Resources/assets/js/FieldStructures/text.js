import { defaultOption, columnFieldSizes } from '@/ComponentStructures/style-options';

export default {
    type: "Text",
    title: "Text",
    column: 'is-full',
    label: "Text",
    name: "text",
    placeholder: null,
    note: null,
    default_value: "",
    readonly: false,
    disabled: false,
    validation: {
        rules: {
            required: false,
            max: null,
            min: null,
            regex: null,
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
                label: "Minimal Character",
            },
            max: {
                component: "ConfigNumber",
                label: "Maximal Character",
            },
            regex: {
                component: "ConfigInput",
                label: "regex",
            }
        },

    },
    attributes: {
        label: "Attributes",
        config: {
            disabled: {
                component: "ConfigCheckbox",
                label: "Is Disabled?",
                settings: {
                    disableBasedOn: 'validation.rules.required'
                }
            },
            readonly: {
                component: "ConfigCheckbox",
                label: "Is Readonly?",
                settings: {
                    disableBasedOn: 'validation.rules.required'
                }
            },
        },
    },
};
