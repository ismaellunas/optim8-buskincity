import {
    dimension
} from './global-configs.js';

export default {
    title: 'Video',
    componentName: 'Video',
    content: {},
    config: {
        video: {
            url: null,
        },
        dimension: dimension.config
    }
};

export const config = {
    video: {
        label: "Video",
        config: {
            url: {
                component: "ConfigInput",
                label: "URL",
                settings: {
                    note: "E.g: https://vimeo.com/553766867",
                    placeholder: "Youtube/Vimeo Video URL",
                },
            },
        }
    },
    dimension: dimension.component
};
