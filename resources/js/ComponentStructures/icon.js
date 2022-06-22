import {
    alignments,
    defaultOption,

} from './style-options';

export default {
    title: 'Icon',
    componentName: 'Icon',
    content: {},
    config: {
        icon: {
            class: null,
            color: null,
            alignment: null,
        },
        style: {
            size: null,
        }
    }
};

export const config = {
    icon: {
        label: "Icon",
        config: {
            class: {
                label: "Class",
                component: "InputIcon",
            },
            alignment: {
                type: "select",
                label: "Alignment",
                options: defaultOption.concat(alignments),
            },
        }
    },
    style: {
        label: "Style",
        config: {
            size: {
                label: "Size",
                component: "InputAddons",
                settings: {
                    addons: 'px',
                },
            },
        }
    },
};
