import { alignments, contentSizes, defaultOption, fixedSquares, imageRatios } from './style-options';

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
        image: {
            // figure
            fixedSquare: null, // is-16x16, etc..
            ratio: "is-4by3", // is-square
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
}

export const config = {
    image: {
        label: "Image",
        config: {
            ratio: {
                type: "select",
                label: "Ratio",
                options: defaultOption.concat(imageRatios)
            },
            fixedSquare: {
                type: "select",
                label: "Fixed Square",
                options: defaultOption.concat(fixedSquares)
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
                type: "select",
                label: "Size",
                options: defaultOption.concat(contentSizes)
            },
            alignment: {
                type: "select",
                label: "Alignment",
                options: defaultOption.concat(alignments)
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
