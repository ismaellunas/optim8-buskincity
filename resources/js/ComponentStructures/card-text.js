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
            size: "",
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
                options: [
                    { value: "is-small", name: "Small" },
                    { value: "is-normal", name: "Normal" },
                    { value: "is-medium", name: "Medium" },
                    { value: "is-large", name: "Large" },
                ]
            }
        }
    }
};
