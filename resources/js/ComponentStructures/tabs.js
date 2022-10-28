import { contentSizes, defaultOption } from './style-options';

import {
    dimension
} from './global-configs.js';

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
        dimension: dimension.config
    }
};

export const config = {
    tabs: {
        label: "Tabs",
        config: {
            alignment: {
                component: "ConfigSelect",
                label: "Alignment",
                settings: {
                    options: defaultOption.concat(
                        [
                            { value: "is-centered", name: "Centered"},
                            { value: "is-right", name: "Right"},
                        ]
                    ),
                },
            },
            size: {
                component: "ConfigSelect",
                label: "Size",
                settings: {
                    options: defaultOption.concat(contentSizes),
                },
            },
            style: {
                component: "ConfigSelect",
                label: "Style",
                settings: {
                    options: defaultOption.concat(
                        [
                            { value: "is-boxed", name: "Boxed"},
                            { value: "is-toggle", name: "Toggle"},
                            { value: "is-toggle is-toggle-rounded", name: "Toggle Rounded"},
                        ]
                    )
                },
            },
            width: {
                component: "ConfigSelect",
                label: "Width",
                settings: {
                    options: defaultOption.concat(
                        [
                            { value: "is-fullwidth", name: "Fullwidth"},
                        ]
                    )
                },
            },
        }
    },
    dimension: dimension.component
};
