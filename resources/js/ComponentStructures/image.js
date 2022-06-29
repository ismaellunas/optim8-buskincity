import { defaultOption, imageRatios, fixedSquares } from './style-options';

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
            rounded: "",
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
            fixedSquare: {
                type: "select",
                label: "FixedSquare",
                options: defaultOption.concat(fixedSquares)
            },
            ratio: {
                type: "select",
                label: "Ratio",
                options: defaultOption.concat(imageRatios)
            },
            rounded: {
                type: "select",
                label: "Rounded",
                options: [
                    { value: "", name: "No"},
                    { value: "is-rounded", name: "Yes"},
                ],
            }
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
