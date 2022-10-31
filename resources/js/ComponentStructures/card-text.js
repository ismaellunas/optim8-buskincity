import { contentSizes, defaultOption, roundedSizes } from './style-options';

import {
    dimension
} from './global-configs.js';

export default {
    title: 'Card Text',
    componentName: 'CardText',
    content: {
        cardContent: {
            content: {
                html: "",
            },
            media: {},
        }
    },
    config: {
        card: {
            rounded: null,
        },
        content: {
            size: null,
        },
        dimension: dimension.config
    }
}

export const config = {
    card: {
        label: "Card",
        config: {
            rounded: {
                component: "ConfigSelect",
                label: "Rounded Size",
                settings: {
                    options: defaultOption.concat(roundedSizes),
                },
            },
        },
    },
    content: {
        label: "Content",
        config: {
            size: {
                component: "ConfigSelect",
                label: "Size",
                settings: {
                    options: defaultOption.concat(contentSizes),
                },
            }
        }
    },
    dimension: dimension.component
};
