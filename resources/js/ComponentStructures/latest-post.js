import { contentSizes, defaultOption, alignments } from './style-options';

export default {
    title: 'Latest Post',
    componentName: 'LatestPost',
    content: {},
    config: {
        post: {
            categoryId: null,
            limit: 3,
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
    post: {
        label: "Latest Post",
        config: {
            categoryId: {
                component: "Select",
                label: "Category",
                settings: {
                    optionsRoute: "admin.api.page-builder.post.category-options",
                },
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
