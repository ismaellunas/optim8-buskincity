import { config as email } from './email';
import { config as fileUpload } from './file-upload';
import { config as number } from './number';
import { config as phone } from './phone';
import { config as select } from './select';
import { config as text } from './text';
import { config as textarea } from './textarea';

export default {
    email: email,
    fileDragDrop: fileUpload,
    number: number,
    phone: phone,
    select: select,
    text: text,
    textarea: textarea,
};
