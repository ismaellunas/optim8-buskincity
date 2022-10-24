export default {
    title: 'Video',
    componentName: 'Video',
    content: {},
    config: {
        video: {
            url: null,
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
};

export const config = {
    video: {
        label: "Video",
        config: {
            url: {
                type: "input",
                label: "URL",
                note: "E.g: https://vimeo.com/553766867",
                placeholder: "Youtube/Vimeo Video URL",
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
