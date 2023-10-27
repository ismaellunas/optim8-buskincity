import icons from "./icon-list.json";
import { last } from 'lodash';

export function iconFormatter(iconClasses) {
    const iconStyle = process.env.fontawesomeFree ? 'fa-solid' : 'fa-light';
    const iconClass = process.env.fontawesomeFree ? iconClasses[0] : last(iconClasses);

    return iconStyle + ' ' + iconClass;
}

export const add = iconFormatter(icons.add);
export const angleDown = iconFormatter(icons.angleDown);
export const angleUp = iconFormatter(icons.angleUp);
export const back = iconFormatter(icons.back);
export const bracketCurly = iconFormatter(icons.bracketCurly);
export const bracketCurlyRight = iconFormatter(icons.bracketCurlyRight);
export const buildingCheck = iconFormatter(icons.buildingCheck);
export const calendar = iconFormatter(icons.calendar);
export const calendarCirclePlus = iconFormatter(icons.calendarCirclePlus);
export const camera = iconFormatter(icons.camera);
export const checkCircle = iconFormatter(icons.checkCircle);
export const circleQuestion = iconFormatter(icons.circleQuestion);
export const city = iconFormatter(icons.city);
export const clear = iconFormatter(icons.clear);
export const close = iconFormatter(icons.close);
export const copy = iconFormatter(icons.copy);
export const crop = iconFormatter(icons.crop);
export const desktop = iconFormatter(icons.desktop);
export const download = iconFormatter(icons.download);
export const duration = iconFormatter(icons.duration);
export const edit = iconFormatter(icons.edit);
export const ellipsis = iconFormatter(icons.ellipsis);
export const eraser = iconFormatter(icons.eraser);
export const expand = iconFormatter(icons.expand);
export const eye = iconFormatter(icons.eye);
export const eyeSlash = iconFormatter(icons.eyeSlash);
export const file = iconFormatter(icons.file);
export const fileExcel = iconFormatter(icons.fileExcel);
export const filePdf = iconFormatter(icons.filePdf);
export const filePowerpoint = iconFormatter(icons.filePowerpoint);
export const fileVideo = iconFormatter(icons.fileVideo);
export const fileWord = iconFormatter(icons.fileWord);
export const flipHorizontal = iconFormatter(icons.flipHorizontal);
export const flipVertical = iconFormatter(icons.flipVertical);
export const globe = iconFormatter(icons.globe);
export const grid = iconFormatter(icons.grid);
export const idCard = iconFormatter(icons.idCard);
export const image = iconFormatter(icons.image);
export const list = iconFormatter(icons.list);
export const locationMark = iconFormatter(icons.locationMark);
export const mobile = iconFormatter(icons.mobile);
export const move = iconFormatter(icons.move);
export const plusCircle = iconFormatter(icons.plusCircle);
export const preview = iconFormatter(icons.preview);
export const rectangleList = iconFormatter(icons.rectangleList);
export const recycle = iconFormatter(icons.recycle);
export const remove = iconFormatter(icons.remove);
export const rotateLeft = iconFormatter(icons.rotateLeft);
export const rotateRight = iconFormatter(icons.rotateRight);
export const search = iconFormatter(icons.search);
export const show = iconFormatter(icons.show);
export const signIn = iconFormatter(icons.signIn);
export const sort = iconFormatter(icons.sort);
export const sortDown = iconFormatter(icons.sortDown);
export const sortUp = iconFormatter(icons.sortUp);
export const suspend = iconFormatter(icons.suspend);
export const tablet = iconFormatter(icons.tablet);
export const timezone = iconFormatter(icons.timezone);
export const unsuspend = iconFormatter(icons.unsuspend);
export const upload = iconFormatter(icons.upload);
export const user = iconFormatter(icons.user);

export default {
    add,
    angleDown,
    angleUp,
    back,
    bracketCurly,
    bracketCurlyRight,
    buildingCheck,
    calendar,
    calendarCirclePlus,
    camera,
    checkCircle,
    circleQuestion,
    city,
    clear,
    close,
    copy,
    crop,
    desktop,
    download,
    duration,
    edit,
    ellipsis,
    eraser,
    expand,
    eye,
    eyeSlash,
    file,
    fileExcel,
    filePdf,
    filePowerpoint,
    fileVideo,
    fileWord,
    flipHorizontal,
    flipVertical,
    globe,
    grid,
    idCard,
    image,
    list,
    locationMark,
    mobile,
    move,
    plusCircle,
    preview,
    rectangleList,
    recycle,
    remove,
    rotateLeft,
    rotateRight,
    search,
    show,
    signIn,
    sort,
    sortDown,
    sortUp,
    suspend,
    tablet,
    timezone,
    unsuspend,
    upload,
    user,
}
