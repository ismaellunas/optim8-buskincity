export default {
    title: "Card",
    componentName: "Card",
    content: {
        cardImage: {
            figure: {
                image: {
                    id: null,
                    src: "",
                    attrs: [],
                },
                //attrs: {
                //    class: [],
                //},
            }
        },
        cardContent: {
            content: {
                html: "",
                attributes: {
                    class: [],
                },
            },
            media: {},
        },
        imagesIds: []
    },
    config: {
        image: {
            // figure
            fixedSquare: "", // is-16x16, etc..
            ratio: "is-4by3", // is-square
            padding: {
                top: null,
                right: null,
                bottom: null,
                left: null,
            }
        }
    }
}

export const config = {
    image: {
        label: "Image",
        config: {
            ratio: {
                type: "select",
                label: "Ratio",
                options: [
                    { value: "", name: "- Empty -" },
                    { value: "is-square", name: "Square"},
                    { value: "is-1by1", name: "1 by 1"},
                    { value: "is-5by4", name: "5 by 4"},
                    { value: "is-4by3", name: "4 by 3"},
                    { value: "is-3by2", name: "3 by 2"},
                    { value: "is-5by3", name: "5 by 3"},
                    { value: "is-16by9", name: "16 by 9"},
                    { value: "is-2by1", name: "2 by 1"},
                    { value: "is-3by1", name: "3 by 1"},
                    { value: "is-4by5", name: "4 by 5"},
                    { value: "is-3by4", name: "3 by 4"},
                    { value: "is-2by3", name: "2 by 3"},
                    { value: "is-3by5", name: "3 by 5"},
                    { value: "is-9by16", name: "9 by 16"},
                    { value: "is-1by2", name: "1 by 2"},
                    { value: "is-1by3", name: "1 by 3"},
                ],
            },
            fixedSquare: {
                type: "select",
                label: "Fixed Square",
                options: [
                    { value: "", name: "- Empty -" },
                    { value: "is-16x16", name: "16x16 px" },
                    { value: "is-24x24", name: "24x24 px" },
                    { value: "is-32x32", name: "32x32 px" },
                    { value: "is-48x48", name: "48x48 px" },
                    { value: "is-64x64", name: "64x64 px" },
                    { value: "is-96x96", name: "96x96 px" },
                    { value: "is-128x128", name: "128x128 px" },
                ],
            },
            padding: {
                label: "Padding",
                component: "TRBL",
            }
        }
    },
    /*
    content: {
        label: "Content",
    },
    general: {
        label: "General",
    }
    */
};
