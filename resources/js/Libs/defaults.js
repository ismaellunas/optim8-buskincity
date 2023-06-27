export const acceptedFileTypes = [
    '.doc',
    '.docx',
    '.pdf',
    '.jpeg',
    '.jpg',
    '.png',
    '.gif',
    '.ppt',
    '.pptx',
    '.xls',
    '.xlsx',
    '.mp4',
    '.mov',
    '.avi',
    '.mpg',
    '.ogv',
    '.3gp',
];

export const acceptedImageTypes = [
    '.jpeg',
    '.jpg',
    '.png',
    '.gif',
];

export const acceptedVideoTypes = [
    '.mp4',
    '.mov',
    '.avi',
    '.mpg',
    '.ogv',
    '.3gp',
];

export const acceptedDocumentTypes = [
    '.pdf',
    '.doc',
    '.docx',
];

export const acceptedSpreadsheetTypes = [
    '.xls',
    '.xlsx',
];

export const acceptedPresentationTypes = [
    '.ppt',
    '.pptx',
];

export const acceptedImportTypes = [
    '.csv',
];

export const acceptedFileGroups = {
    image: acceptedImageTypes,
    video: acceptedVideoTypes,
    document: acceptedDocumentTypes,
    spreadsheet: acceptedSpreadsheetTypes,
    presentation: acceptedPresentationTypes,
    import: acceptedImportTypes,
};

export const acceptedImageMimes = [
    'image/jpeg',
    'image/jpg',
    'image/png',
    'image/gif',
];

export const debounceTime = 750;

export const appName = import.meta.env.VITE_APP_NAME;

export const oneMegabyte = 1024;

export const userImage = '/images/profile-picture-default.jpg';

export const pageStatus = {
    draft: 0,
    published: 1,
};

export const loadingOptions = {
    color:'#3280bf',
    loader: 'dots',
    opacity: 0.3,
    zIndex: 8000,
};
