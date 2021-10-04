import { contentSizes, defaultOption, alignments, colors } from './style-options';

export default {
    title: 'Message',
    componentName: 'Message',
    content: {
        html: null,
    },
    config: {
        message: {
            size: null,
            color: null,
        }
    }
};

export const config = {
    message: {
        label: "Message",
        config: {
            size: {
                type: "select",
                label: "Size",
                options: defaultOption.concat(contentSizes)
            },
            color: {
                type: "select",
                label: "Color",
                options: defaultOption.concat(colors)
            },
        }
    }
};
