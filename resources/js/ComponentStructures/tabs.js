import { contentSizes, defaultOption } from './style-options';

const dummyText = "<p>Lorem ipsum dolor sit amet consectetur, adipisicing elit. Ad, quidem iure qui, dignissimos necessitatibus commodi laborum nisi atque quo quos libero pariatur deleniti natus laboriosam fuga, nemo non sequi tempore!</p>";

export default {
    title: 'Tabs',
    componentName: 'Tabs',
    content: {
        tabs: [
            {
                name: 'First Tab',
                icon: null,
                html: dummyText,
            },
            {
                name: 'Second Tab',
                icon: null,
                html: dummyText,
            },
            {
                name: 'Third Tab',
                icon: null,
                html: dummyText,
            },
        ],
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
