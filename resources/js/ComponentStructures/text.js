import { contentSizes, defaultOption } from './style-options';

export default {
    title: 'Text',
    componentName: 'Text',
    content: {
        html: null,
    },
    config: {
        text: {
            size: null,
        }
    }
};

export const config = {
    text: {
        label: "Text",
        config: {
            size: {
                type: "select",
                label: "Size",
                options: defaultOption.concat(contentSizes)
            },
        }
    }
};
