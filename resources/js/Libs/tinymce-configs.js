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
    toolbar: 'formatselect | bold italic link | bullist numlist | table',
};

export const fullEditorConfig = {
    //inline: false,
    height: 300,
    menubar: false,
    branding: false,
    plugins: [
        'advlist autolink lists link image charmap print preview anchor',
        'searchreplace visualblocks code fullscreen',
        'insertdatetime media table paste code help wordcount'
    ],
    block_formats: 'Paragraph=p; Header 1=h1; Header 2=h2; Header 3=h3',
    toolbar: 'undo redo | formatselect | ' +
    'bold italic backcolor | alignleft aligncenter ' +
    'alignright alignjustify | bullist numlist outdent indent | ' +
    'removeformat ',
    content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
};
