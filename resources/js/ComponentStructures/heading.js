export default {
    title: "Heading",
    componentName: "Heading",
    content: {
        heading: {
            html: "",
        },
    },
    config: {
        heading: {
            tag: "h1",
            type: "title",
        }
    }
};

export const config = {
    heading: {
        label: "Heading",
        config: {
            tag: {
                type: "select",
                label: "Heading",
                options: [
                    { value: "h1", name: "H1" },
                    { value: "h2", name: "H2" },
                    { value: "h3", name: "H3" },
                    { value: "h4", name: "H4" },
                    { value: "h5", name: "H5" },
                    { value: "h6", name: "H6" },
                ],
            },
            type: {
                type: "select",
                label: "Type",
                options: [
                    { value: "title", name: "Title"},
                    { value: "subtitle", name: "Subtitle"},
                ],
            }
            /*
            size: {
                type: "select",
                label: "Size",
                options: [
                    { value: "is-1", name: "1"},
                    { value: "is-2", name: "2"},
                    { value: "is-3", name: "3"},
                    { value: "is-4", name: "4"},
                    { value: "is-5", name: "5"},
                    { value: "is-6", name: "6"},
                ],
            },
            */
        },
    }
};
