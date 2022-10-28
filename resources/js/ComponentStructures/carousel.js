import { imageRatios } from './style-options';

import {
    dimension
} from './global-configs.js';

export default {
    title: 'Carousel',
    componentName: 'Carousel',
    content: {
        carousel: {
            images: [
                {
                    mediaId: null,
                }
            ],
        },
        template: {
            mediaId: null,
        },
    },
    config: {
        carousel: {
            numberOfSliders: 1,
            autoPlay: true,
            // figure
            ratio: "is-16by9",
        },
        dimension: dimension.config
    }
}

export const config = {
    carousel: {
        label: "Carousel",
        config: {
            numberOfSliders: {
                component: "ConfigSelect",
                label: "Number of Sliders",
                settings: {
                    options: [
                        { value: 1, name: "1"},
                        { value: 2, name: "2"},
                        { value: 3, name: "3"},
                        { value: 4, name: "4"},
                        { value: 5, name: "5"},
                        { value: 6, name: "6"},
                    ],
                },
            },
            autoPlay: {
                component: "ConfigSelect",
                label: "Auto Play",
                settings: {
                    options: [
                        { value: false, name: "No"},
                        { value: true, name: "Yes"},
                    ],
                },
            },
            ratio: {
                component: "ConfigSelect",
                label: "Ratio",
                settings: {
                    options: imageRatios
                },
            },
        }
    },
    dimension: dimension.component
};
