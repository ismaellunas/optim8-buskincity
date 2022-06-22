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
            size: null,
            alignment: null,
        },
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
            size: {
                label: "Size",
                component: "InputAddons",
                settings: {
                    isNumber: true,
                    addons: 'px',
                },
            },
            alignment: {
                type: "select",
                label: "Alignment",
                options: defaultOption.concat(alignments),
            },
        }
    },
};
