export let common = {
    toolbar: [ 'heading', '|', 'bold', 'italic', 'link', '|', 'alignment', 'bulletedList', 'numberedList', 'blockQuote', ],
    //autoParagraph: false,
    //enterMode: 2,
    //shiftEnterMode: 1,
    heading: {
        options: [
            { model: 'paragraph', title: 'Paragraph', class: '' },
            { model: 'heading1', view: 'h1', title: 'Heading 1' },
            { model: 'heading2', view: 'h2', title: 'Heading 2' }
        ]
    },
};

export let heading = {
    toolbar: [],
};
