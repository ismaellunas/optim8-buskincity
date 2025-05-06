import { defaultOption } from './style-options';

import {
    dimension
} from './global-configs.js';

export default {
    title: "User List",
    componentName: "UserList",
    content: {
        heading: {
            html: "User List",
        },
        roles: [],
    },
    config: {
        list: {
            roles: [],
            excludedId: null,
            orderBy: null,
            countries: [],
            types: [],
        },
        dimension: dimension.config
    }
};

export const config = {
    list: {
        label: "User List",
        config: {
            roles: {
                component: "ConfigCheckboxes",
                label: "Role",
                settings: {
                    optionsRoute: "admin.api.page-builder.user-list.role-options",
                },
            },
            excludedId: {
                component: "ConfigInput",
                label: "Exclude User ID",
            },
            orderBy: {
                component: "ConfigSelect",
                label: "Order By",
                settings: {
                    options: defaultOption.concat(
                        [
                            { value: "first_name-asc", name: "A-Z"},
                            { value: "first_name-desc", name: "Z-A"},
                            { value: "random", name: "Random"},
                            { value: "created_at-asc", name: "Date"},
                        ]
                    ),
                },
            },
            countries: {
                component: "ConfigSelectMultiple",
                label: "Country",
                settings: {
                    optionsRoute: "admin.api.page-builder.country-options",
                },
            },
            types: {
                component: "ConfigSelectMultiple",
                label: "Types",
                settings: {
                    optionsRoute: "admin.api.page-builder.type-options",
                },
            },
        },
    },
    dimension: dimension.component
};
