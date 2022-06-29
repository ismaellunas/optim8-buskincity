import { defaultOption } from './style-options';

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
    list: {
        label: "User List",
        config: {
            roles: {
                component: "Checkboxes",
                label: "Role",
                settings: {
                    optionsRoute: "admin.api.page-builder.user-list.role-options",
                },
            },
            excludedId: {
                type: "input",
                label: "Exclude User ID",
            },
            orderBy: {
                type: "select",
                label: "Order By",
                options: defaultOption.concat(
                    [
                        { value: "first_name-asc", name: "A-Z"},
                        { value: "first_name-desc", name: "Z-A"},
                        { value: "random", name: "Random"},
                        { value: "created_at-asc", name: "Date"},
                    ]
                ),
            },
            countries: {
                component: "SelectMultiple",
                label: "Country",
                settings: {
                    optionsRoute: "admin.api.page-builder.country-options",
                },
            },
        },
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
