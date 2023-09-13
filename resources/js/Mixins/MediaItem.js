import icon from '@/Libs/icon-class';
import { isPromise } from '@/Libs/utils';

export default {
    data() {
        return {
            icon,
        };
    },

    computed: {
        async isImage() {
            if (this.medium.hasOwnProperty('is_image')) {
                return this.medium.is_image;
            }

            if (isPromise(this.medium?.file)) {
                let promiseFile = await this.medium.file;

                return promiseFile.type.startsWith("image");
            } else {
                return this.medium.file.type.startsWith("image");
            }
        },

        thumbnailIcon() {
            if (this.medium.file_type === "video") {
                return this.icon.fileVideo;
            } else if (this.medium.extension) {
                if (this.medium.extension === "pdf") {
                    return this.icon.filePdf;
                } else if (this.medium.extension.startsWith('doc')) {
                    return this.icon.fileWord;
                } else if (this.medium.extension.startsWith('ppt')) {
                    return this.icon.filePowerpoint;
                } else if (this.medium.extension.startsWith('xls')) {
                    return this.icon.fileExcel;
                }
            }

            return this.icon.file;
        }
    },
};
