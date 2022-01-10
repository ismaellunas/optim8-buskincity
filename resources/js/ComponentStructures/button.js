import {
    colors,
    contentSizes,
    defaultOption,
    otherColors
} from './style-options';

export default {
    title: 'Button',
    componentName: 'Button',
    content: {
        button: {
            text: "Button",
            icon: null,
        },
    },
    config: {
        button: {
            link: null,
            target: null,
            color: null,
            isLight: false,
            size: null,
            width: null,
            style: null,
            iconPosition: 'left',
        },
    }
};

export const config = {
    button: {
        label: "Tabs",
        config: {
            link: {
                type: "input",
                label: "Link",
            },
            target: {
                type: "select",
                label: "Target",
                options: defaultOption.concat(
                    [
                        { value: "_blank", name: "New window"},
                        { value: "download", name: "Download"},
                    ]
                ),
            },
            color: {
                type: "select",
                label: "Color",
                options: defaultOption.concat(otherColors, colors),
            },
            isLight: {
                type: "checkbox",
                label: "Light Button?",
            },
            size: {
                type: "select",
                label: "Size",
                options: defaultOption.concat(contentSizes),
            },
            width: {
                type: "select",
                label: "Width",
                options: defaultOption.concat(
                    [
                        { value: "is-fullwidth", name: "Fullwidth"},
                    ]
                ),
            },
            style: {
                type: "select",
                label: "Style",
                options: defaultOption.concat(
                    [
                        { value: "is-rounded", name: "Rounded"},
                        { value: "is-outlined", name: "Outlined"},
                        { value: "is-inverted", name: "Inverted"},
                        { value: "is-rounded is-outlined", name: "Rounded & Outlined"},
                    ]
                ),
            },
            iconPosition: {
                type: "select",
                label: "Icon Position",
                options: defaultOption.concat(
                    [
                        { value: "left", name: "Left"},
                        { value: "right", name: "Right"},
                    ]
                ),
            },
        }
    },
};
