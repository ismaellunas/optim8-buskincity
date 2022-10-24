export const textComponent = {
    //height: 500,
    //toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | help',
    inline: true,
    menubar: false,
    branding: false,
    plugins: [
        'advlist autolink lists link image charmap',
        'searchreplace visualblocks code fullscreen',
        'print preview anchor insertdatetime media',
        'paste code help wordcount table'
    ],
    block_formats: 'Paragraph=p; Header 1=h1; Header 2=h2; Header 3=h3',
    toolbar: 'formatselect | bold italic link | alignleft aligncenter alignright alignjustify | bullist numlist | table',
    formats: {
        bold: { inline: 'b' }
    },
    extended_valid_elements:'b',
    invalid_elements:'strong'
};

export const emailConfig = {
    plugins: 'link lists',
    toolbar: 'formatselect | bold italic forecolor backcolor link ' +
        '| align bullist numlist | removeformat',
    menubar: false,
    target_list: false,
    object_resizing : false,
    block_formats: (
        'Paragraph=p; '+
        'Header 1=h1; '+
        'Header 2=h2; '+
        'Header 3=h3'
    ),
    toolbar_mode: "wrap",
    noneditable_regexp: /\{\{[^\}]+\}\}/g,
};

export const fullConfig = {
    height: 500,
    menubar: false,
    plugins: [
        'advlist autolink lists link image charmap print preview ' +
        'searchreplace visualblocks fullscreen',
        'insertdatetime media table paste wordcount hr'
    ],
    block_formats: (
        'Paragraph=p; '+
        'Header 1=h1; '+
        'Header 2=h2; '+
        'Header 3=h3'
    ),
    toolbar1: (
        'fullscreen | formatselect | ' +
        'bold italic underline strikethrough blockquote | ' +
        'forecolor backcolor | ' +
        'removeformat image media'
    ),
    toolbar2: (
        'alignleft aligncenter alignright alignjustify | ' +
        'bullist numlist outdent indent hr | ' +
        'link table charmap'
    ),
};

export const apiKey = process.env.MIX_TINYMCE_API_KEY;
