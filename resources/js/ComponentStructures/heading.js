export default {
    title: 'Heading',
    componentName: 'Heading',
    content: {
        heading: {
            html: '',
        },
    },
    config: {
        tag: "h1",
        size: "is-1",
        type: "title",
    }
}

export const config = {
    tag: {
        type: "select",
        label: "Heading",
        options: [
            { value: 'h1', name: 'Heading 1' },
            { value: 'h2', name: 'Heading 2' },
            { value: 'h3', name: 'Heading 3' },
        ],
    },
    size: {
        type: "select",
        label: "Size",
        options: [
            { value: 'is-1', name: '1'},
            { value: 'is-2', name: '2'},
            { value: 'is-3', name: '3'},
            { value: 'is-4', name: '4'},
            { value: 'is-5', name: '5'},
            { value: 'is-6', name: '6'},
        ],
    },
    type: {
        type: "select",
        label: "Type",
        options: [
            { value: 'title', name: 'Title'},
            { value: 'subtitle', name: 'Subtitle'},
        ],
    },
};
