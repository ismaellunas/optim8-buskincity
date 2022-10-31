import {
    defaultOption,
    imageRatios,
    fixedSquares,
    roundedSizes
} from './style-options';

import {
    dimension
} from './global-configs.js';

export default {
    title: 'Image',
    componentName: 'Image',
    content: {
        figure: {
            image: {
                mediaId: null,
            },
        },
    },
    config: {
        image: {
            // figure
            fixedSquare: null,
            ratio: "is-4by3",
            // img
            rounded: null,
        },
        dimension: dimension.config
    }
}

export const config = {
    image: {
        label: "Image",
        config: {
            fixedSquare: {
                component: "ConfigSelect",
                label: "FixedSquare",
                settings: {
                    options: defaultOption.concat(fixedSquares)
                },
            },
            ratio: {
                component: "ConfigSelect",
                label: "Ratio",
                settings: {
                    options: defaultOption.concat(imageRatios)
                },
            },
            rounded: {
                component: "ConfigSelect",
                label: "Rounded Size",
                settings: {
                    options: defaultOption.concat(roundedSizes),
                },
            },
        }
    },
    dimension: dimension.component
};
