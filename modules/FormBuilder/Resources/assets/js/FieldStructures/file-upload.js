import { defaultOption, columnFieldSizes } from '@/ComponentStructures/style-options';
import { maxFileSize as maxFileSizeResponse } from '@/Libs/settings';

const maxFileSize = await maxFileSizeResponse();

export default {
    type: "FileDragDrop",
    title: "File Upload",
    column: 'is-full',
    label: "File",
    name: "file",
    placeholder: "Drop files here...",
    notes: [],
    default_value: [],
    max_file_number: 1,
    min_file_number: 0,
    image_dimensions: {
        width: null,
        height: null,
    },
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
    customValidation: {
        component: "ConfigFileUploadValidation",

    },
    attributes: {
        component: "ConfigFileUploadAttribute",
    },
};
