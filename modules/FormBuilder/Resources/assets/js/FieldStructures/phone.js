import { defaultOption, columnFieldSizes } from '@/ComponentStructures/style-options';

export default {
    type: "Phone",
    title: "Phone",
    column: 'is-full',
    label: "",
    name: "",
    placeholder: null,
    notes: [],
    default_value: {
        country: null,
        number: null
    },
    readonly: false,
    disabled: false,
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
            placeholder: {
                component: "ConfigInput",
                label: "Placeholder",
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
    validation: {
        label: "Validation",
        config: {
            required: {
                component: "ConfigCheckbox",
                label: "Is Required?",
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
