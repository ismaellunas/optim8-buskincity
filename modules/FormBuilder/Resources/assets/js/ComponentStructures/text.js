export default {
    title: 'Text',
    componentName: 'Text',
    config: {
        properties: {
            name: null,
            label: 'Text',
            placeholder: null,
        },
        data: {
            default: null,
        },
        validation: {
            required: false,
            minLength: null,
            maxLength: null,
            regex: null,
        },
        attributes: {
            disabled: false,
            readonly: false,
        }
    }
};

export const config = {
    properties: {
        label: "Properties",
        config: {
            name: {
                type: "input",
                label: "Name",
            },
            label: {
                type: "input",
                label: "Label",
            },
            placeholder: {
                type: "input",
                label: "Placeholder",
            },
        }
    },
    data: {
        label: "Data",
        config: {
            default: {
                type: "input",
                label: "Default Value",
            }
        },
    },
    validation: {
        label: "Validation",
        config: {
            required: {
                type: "checkbox",
                label: "Required",
            },
            minLength: {
                type: "input",
                label: "Min Length",
            },
            maxLength: {
                type: "input",
                label: "Max Length",
            },
            regex: {
                type: "input",
                label: "regex",
            }
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
