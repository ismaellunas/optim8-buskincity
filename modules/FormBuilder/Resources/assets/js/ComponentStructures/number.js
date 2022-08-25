export default {
    title: 'Number',
    componentName: 'Number',
    config: {
        properties: {
            name: null,
            label: 'Number',
            placeholder: null,
        },
        data: {
            default: null,
        },
        validation: {
            required: false,
            min: null,
            max: null,
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
            min: {
                type: "input",
                label: "Min Length",
            },
            max: {
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
