import { alignments, contentSizes, defaultOption, fixedSquares, imageRatios } from './style-options';

export default {
    title: "Card",
    componentName: "Card",
    content: {
        cardImage: {
            figure: {
                image: {
                    id: null,
                    mediaId: null,
                    src: "",
                    attrs: [],
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
        wrapper: {
            margin: {
                top: null,
                right: null,
                bottom: null,
                left: null,
            },
            padding: {
                top: null,
                right: null,
                bottom: null,
                left: null,
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
    wrapper: {
        label: "Wrapper",
        isOpen: false,
        config: {
            margin: {
                component: "TRBL",
                label: "Margin",
            },
            padding: {
                component: "TRBL",
                label: "Padding",
            }
        }
    }
};
