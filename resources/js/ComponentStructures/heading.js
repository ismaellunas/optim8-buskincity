import { alignments, defaultOption, textColors } from './style-options';

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
            alignment: null,
            color: null,
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
            },
            alignment: {
                type: "select",
                label: "Alignment",
                options: defaultOption.concat(alignments),
            },
            color: {
                type: "select",
                label: "Text Color",
                options: defaultOption.concat(textColors),
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
