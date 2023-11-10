import { defaultOption, columnFieldSizes } from '@/ComponentStructures/style-options';
import { maxFileSize as maxFileSizeResponse } from '@/Libs/settings';

const maxFileSize = await maxFileSizeResponse();

export default {
    type: "FileDragDrop",
    title: "File Upload",
    column: 'is-full',
    label: "",
    name: "",
    placeholder: "Drop files here...",
    notes: [],
    default_value: [],
    max_file_number: 1,
    min_file_number: 0,
    media_dimension: {
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
    is_image_editor_enabled: false,
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
    imageEditor: {
        component: "ConfigFileUploadImageEditor",

    },
    attributes: {
        component: "ConfigFileUploadAttribute",
    },
};
