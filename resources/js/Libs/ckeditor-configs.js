export let common = {
    toolbar: [ 'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', ],
    heading: {
        options: [
            { model: 'paragraph', title: 'Paragraph', class: '' },
            { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'title is-1' },
            { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'title is-2' }
        ]
    },
};

export let heading = {
    toolbar: [],
};
