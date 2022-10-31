export const iconType = 'fa-light';

export const add = iconFormatter('fa-plus');
export const angleDown = iconFormatter('fa-angle-down');
export const angleUp = iconFormatter('fa-angle-up');
export const back = iconFormatter('fa-arrow-left');
export const bracketCurly = iconFormatter('fa-bracket-curly');
export const bracketCurlyRight = iconFormatter('fa-bracket-curly-right');
export const buildingCheck = iconFormatter('fa-building-circle-check');
export const checkCircle = iconFormatter('fa-check-circle');
export const clear = iconFormatter('fa-times');
export const close = iconFormatter('fa-xmark');
export const copy = iconFormatter('fa-copy');
export const download = iconFormatter('fa-download');
export const edit = iconFormatter('fa-pen');
export const ellipsis = iconFormatter('fa-ellipsis-h');
export const eraser = iconFormatter('fa-eraser');
export const expand = iconFormatter('fa-expand');
export const eye = iconFormatter('fa-eye');
export const eyeSlash = iconFormatter('fa-eye-slash');
export const grid = iconFormatter('fa-th');
export const idCard = iconFormatter('fa-id-card');
export const image = iconFormatter('fa-image');
export const list = iconFormatter('fa-th-list');
export const locationMark = iconFormatter('fa-location-dot');
export const move = iconFormatter('fa-arrows-alt');
export const plusCircle = iconFormatter('fa-plus-circle');
export const preview = iconFormatter('fa-arrow-up-right-from-square');
export const rectangleList = iconFormatter('fa-rectangle-list');
export const recycle = iconFormatter('fa-recycle');
export const remove = iconFormatter('fa-trash-can');
export const show = iconFormatter('fa-eye');
export const signIn = iconFormatter('fa-sign-in-alt');
export const suspend = iconFormatter('fa-ban');
export const unsuspend = iconFormatter('fa-hands-helping');
export const upload = iconFormatter('fa-upload');
export const user = iconFormatter('fa-user');

export function iconFormatter(iconClass) {
    return iconType + ' ' + iconClass;
}

export default {
    add,
    angleDown,
    angleUp,
    back,
    bracketCurly,
    bracketCurlyRight,
    buildingCheck,
    checkCircle,
    clear,
    close,
    copy,
    download,
    edit,
    ellipsis,
    eraser,
    expand,
    eye,
    eyeSlash,
    grid,
    idCard,
    image,
    list,
    locationMark,
    move,
    plusCircle,
    preview,
    rectangleList,
    recycle,
    remove,
    show,
    signIn,
    suspend,
    unsuspend,
    upload,
    user,
}
