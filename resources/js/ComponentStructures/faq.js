import { defaultOption, alignments, colors } from './style-options';
import { question } from './faq-options';

export default {
    title: 'FAQ',
    componentName: 'Faq',
    content: {
        heroContent: {
            body: {
                title: {
                    html: "FAQ",
                },
            }
        },
        faqContent: {
            contents: [
                question,
                {
                    question: "Test 2",
                    answer: "Testing",
                    childs: [],
                }
            ],
            template: {
                question: question,
            },
        },
    },
    config: {
        hero: {
            alignment: null,
            color: null,
        }
    }
};

export const config = {
    hero: {
        label: "Hero",
        config: {
            alignment: {
                type: "select",
                label: "Alignment",
                options: defaultOption.concat(alignments)
            },
            color: {
                type: "select",
                label: "Color",
                options: defaultOption.concat(colors)
            },
        }
    }
};
