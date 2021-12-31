import { contentSizes, defaultOption } from './style-options';

export default {
    title: 'Tabs',
    componentName: 'Tabs',
    content: {
        tabs: [],
        template: {
            name: null,
            icon: null,
            html: "",
        }
    },
    config: {
        tabs: {
            alignment: null,
            size: null,
            style: null,
            width: null,
        },
    }
};

export const config = {
    tabs: {
        label: "Tabs",
        config: {
            alignment: {
                type: "select",
                label: "Alignment",
                options: defaultOption.concat(
                    [
                        { value: "is-centered", name: "Centered"},
                        { value: "is-right", name: "Right"},
                    ]
                )
            },
            size: {
                type: "select",
                label: "Size",
                options: defaultOption.concat(contentSizes)
            },
            style: {
                type: "select",
                label: "Style",
                options: defaultOption.concat(
                    [
                        { value: "is-boxed", name: "Boxed"},
                        { value: "is-toggle", name: "Toggle"},
                        { value: "is-toggle is-toggle-rounded", name: "Toggle Rounded"},
                    ]
                )
            },
            width: {
                type: "select",
                label: "Width",
                options: defaultOption.concat(
                    [
                        { value: "is-fullwidth", name: "Fullwidth"},
                    ]
                )
            },
        }
    },
};
