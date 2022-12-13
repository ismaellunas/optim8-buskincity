import {
    contentPositions,
    defaultOption,
    fixedSquares,
    imageRatios,
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
            width: null,
            height: null,
            // figure
            fixedSquare: null,
            ratio: null,
            // img
            rounded: null,
            position: null,
        },
        dimension: dimension.config
    }
}

export const config = {
    image: {
        label: "Image",
        config: {
            width: {
                component: "ConfigNumberAddons",
                label: "Width",
                settings: {
                    addons: "px",
                },
            },
            height: {
                component: "ConfigNumberAddons",
                label: "Height",
                settings: {
                    addons: "px",
                },
            },
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
            position: {
                component: "ConfigSelect",
                label: "Position",
                settings: {
                    options: defaultOption.concat(contentPositions),
                },
            },
        }
    },
    dimension: dimension.component
};
