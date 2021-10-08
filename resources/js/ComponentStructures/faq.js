import { defaultOption, alignments } from './style-options';

export default {
    title: 'FAQ',
    componentName: 'Faq',
    content: {
        heading: {
            html: "FAQ",
        },
        faqContent: {
            contents: [],
            template: {
                question: {
                    id: null,
                    question: null,
                    answer: null,
                },
            },
        },
    },
    config: {
        heading: {
            tag: "h1",
            type: "title",
            alignment: null,
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
        },
    }
};
