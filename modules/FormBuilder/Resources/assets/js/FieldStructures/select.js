import { defaultOption, columnFieldSizes } from '@/ComponentStructures/style-options';

export default {
    type: "Select",
    title: "Select",
    column: 'is-full',
    label: "",
    name: "",
    placeholder: null,
    notes: [],
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
            labelName: {
                component: "ConfigLabelName",
            },
            notes: {
                component: "ConfigNotes",
                label: "Notes",
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
                component: "ConfigAddOption",
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
