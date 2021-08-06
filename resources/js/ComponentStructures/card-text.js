import { contentSizes, defaultOption } from './style-options';

export default {
    title: 'Card Text',
    componentName: 'CardText',
    content: {
        cardContent: {
            content: {
                html: "",
                //attributes: {
                //    class: [],
                //},
            },
            media: {},
        }
    },
    config: {
        content: {
            size: null,
        }
    }
}

export const config = {
    content: {
        label: "Content",
        config: {
            size: {
                type: "select",
                label: "Size",
                options: defaultOption.concat(contentSizes),
            }
        }
    }
};
