export default {
    title: 'Select',
    componentName: 'Select',
    config: {
        properties: {
            name: null,
            label: 'Select',
        },
        data: {
            default: null,
            options: [
                {
                    id: '0',
                    value: 'Label'
                }
            ],
        },
        validation: {
            required: false,
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
        }
    },
    data: {
        label: "Data",
        config: {
            default: {
                type: "input",
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
                type: "checkbox",
                label: "Required",
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
