import { defaultOption, columnFieldSizes } from '@/ComponentStructures/style-options';
import { maxFileSize } from '@/Libs/defaults';

export default {
    type: "FileDragDrop",
    title: "File Upload",
    column: 'is-full',
    label: "File",
    name: "file",
    placeholder: "Drop files here...",
    note: null,
    default_value: [],
    max_file_number: 1,
    min_file_number: 0,
    validation: {
        rules: {
            required: false,
            mimes: ['image'],
            max: maxFileSize,
        },
        message: []
    },
    is_multiple_upload: false,
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
                    generateBasedOn: 'label',
                    placeholder: 'field_name'
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
    validation: {
        label: "Validation",
        config: {
            required: {
                component: "ConfigCheckbox",
                label: "Is Required?",
            },
            mimes: {
                component: "ConfigCheckboxes",
                label: "Accepted Type",
                settings: {
                    options: [
                        { id: 'image', value: 'Image' },
                        { id: 'video', value: 'Video' },
                        { id: 'document', value: 'Document' },
                        { id: 'spreadsheet', value: 'Spreadsheet' },
                        { id: 'presentation', value: 'Presentation' },
                    ]
                },
            },
            max: {
                component: "ConfigNumberAddons",
                label: "Maximal File Size",
                settings: {
                    addons: 'KiB',
                    max: maxFileSize,
                    note: `Max file size: ${maxFileSize} KiB`
                }
            },
        },

    },
    attributes: {
        label: "Attributes",
        component: "ConfigFileUploadAttribute",
    },
};
