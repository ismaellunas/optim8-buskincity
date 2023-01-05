export function onFormEditorClicked(event, inputConfigId) {
    if (! (
        event.target.closest('.component-configurable')
        || event.target.closest('.form-builder-input-config')
    )) {
        inputConfigId.value = '';
    }
}

export const acceptedMimes = {
    image: [
        'jpeg',
        'jpg',
        'png',
        'gif',
    ],
    video: [
        'mp4',
        'mov',
        'avi',
        'mpg',
        'ogv',
        '3gp',
    ],
    document: [
        'pdf',
        'doc',
        'docx',
    ],
    spreadsheet: [
        'xls',
        'xlsx',
    ],
    presentation: [
        'ppt',
        'pptx',
    ],
};