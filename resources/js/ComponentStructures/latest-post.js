import {
    defaultOption,
} from './style-options';

import {
    dimension
} from './global-configs.js';

export default {
    title: 'Latest Post',
    componentName: 'LatestPost',
    content: {},
    config: {
        post: {
            categoryId: null,
            limit: 3,
        },
        dimension: dimension.config
    }
};

export const config = {
    post: {
        label: "Latest Post",
        config: {
            categoryId: {
                component: "ConfigSelect",
                label: "Category",
                settings: {
                    optionsRoute: "admin.api.page-builder.post.category-options",
                },
            },
            limit: {
                component: "ConfigSelect",
                label: "Total Post Displayed",
                settings: {
                    options: defaultOption.concat([
                        { value: 1, name: "1" },
                        { value: 2, name: "2" },
                        { value: 3, name: "3" },
                        { value: 4, name: "4" },
                        { value: 5, name: "5" },
                        { value: 6, name: "6" },
                    ]),
                },
            },
        }
    },
    dimension: dimension.component
};
