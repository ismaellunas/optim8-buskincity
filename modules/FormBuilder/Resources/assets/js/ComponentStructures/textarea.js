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
