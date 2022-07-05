import {
    alignments,
    defaultOption,
    textColors,

} from './style-options';

export default {
    title: 'Icon',
    componentName: 'Icon',
    content: {},
    config: {
        icon: {
            class: null,
            alignment: null,
            color: null,
        },
        style: {
            size: null,
        },
        dimension: {
            'style.padding': {
                top: null,
                right: null,
                bottom: null,
                left: null,
                unit: 'px',
            },
            'style.margin': {
                top: null,
                right: null,
                bottom: null,
                left: null,
                unit: 'px',
            },
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
            color: {
                type: "select",
                label: "Color",
                options: defaultOption.concat(textColors),
            },
        }
    },
    style: {
        label: "Style",
        config: {
            size: {
                label: "Size",
                component: "NumberAddons",
                settings: {
                    addons: 'px',
                },
            },
        }
    },
    dimension: {
        label: "Dimension",
        isOpen: false,
        config: {
            'style.margin': {
                component: "TRBLInput",
                label: "Margin",
            },
            'style.padding': {
                component: "TRBLInput",
                label: "Padding",
            },
        }
    }
};
