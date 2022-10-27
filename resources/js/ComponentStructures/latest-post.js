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
        }
    },
    dimension: dimension.component
};
