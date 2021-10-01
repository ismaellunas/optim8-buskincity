import { contentSizes, defaultOption, alignments, colors } from './style-options';

export default {
    title: 'Hero',
    componentName: 'Hero',
    content: {
        body: {
            title: {
                html: null,
            },
            subtitle: {
                html: null,
            },
        }
    },
    config: {
        hero: {
            size: null,
            alignment: null,
            color: null,
        }
    }
};

export const config = {
    hero: {
        label: "Hero",
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
            color: {
                type: "select",
                label: "Color",
                options: defaultOption.concat(colors)
            },
        }
    }
};
