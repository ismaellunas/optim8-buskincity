import {
    alignments,
    contentSizes,
    defaultOption,
    fixedSquares,
    imageRatios,
    roundedSizes,
} from './style-options';

import {
    dimension
} from './global-configs.js';

export default {
    title: "Card",
    componentName: "Card",
    content: {
        cardImage: {
            figure: {
                image: {
                    mediaId: null,
                },
            }
        },
        cardContent: {
            content: {
                html: "",
                attributes: {
                    class: [],
                },
            },
            media: {},
        },
    },
    config: {
        card: {
            link: null,
            rounded: null,
            isShadowless: false,
        },
        image: {
            // figure
            fixedSquare: null, // is-16x16, etc..
            ratio: "is-4by3", // is-square
            rounded: null,
            padding: {
                top: null,
                right: null,
                bottom: null,
                left: null,
            }
        },
        content: {
            alignment: null,
            size: null,
        },
        dimension: dimension.config
    }
}

export const config = {
    card: {
        label: "Card",
        config: {
            link: {
                component: "ConfigInput",
                label: "Link",
                settings: {
                    note: "E.g: https://www.google.com",
                },
            },
            rounded: {
                component: "ConfigSelect",
                label: "Rounded Size",
                settings: {
                    options: defaultOption.concat(roundedSizes),
                },
            },
            isShadowless: {
                component: "ConfigCheckbox",
                label: "Is Shadowless?",
            },
        },
    },
    image: {
        label: "Image",
        config: {
            ratio: {
                component: "ConfigSelect",
                label: "Ratio",
                settings: {
                    options: defaultOption.concat(imageRatios)
                },
            },
            fixedSquare: {
                component: "ConfigSelect",
                label: "Fixed Square",
                settings: {
                    options: defaultOption.concat(fixedSquares)
                },
            },
            rounded: {
                component: "ConfigSelect",
                label: "Rounded Size",
                settings: {
                    options: defaultOption.concat(roundedSizes),
                },
            },
            padding: {
                label: "Padding",
                component: "TRBL",
            },
        }
    },
    content: {
        label: "Content",
        config: {
            size: {
                component: "ConfigSelect",
                label: "Size",
                settings: {
                    options: defaultOption.concat(contentSizes)
                },
            },
            alignment: {
                component: "ConfigSelect",
                label: "Alignment",
                settings: {
                    options: defaultOption.concat(alignments)
                },
            },
        }
    },
    dimension: dimension.component
};
