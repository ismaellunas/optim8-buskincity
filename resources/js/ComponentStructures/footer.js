import { defaultOption, alignments } from './style-options';

export default {
    title: 'Footer',
    componentName: 'Footer',
    content: {
        html: null,
    },
    config: {
        footer: {
            alignment: null,
        }
    }
};

export const config = {
    footer: {
        label: "Footer",
        config: {
            alignment: {
                type: "select",
                label: "Alignment",
                options: defaultOption.concat(alignments),
            },
        }
    }
};
