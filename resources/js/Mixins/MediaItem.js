import icon from '@/Libs/icon-class';

export default {
    data() {
        return {
            icon,
        };
    },

    computed: {
        isImage() {
            if (this.medium.hasOwnProperty('is_image')) {
                return this.medium.is_image;
            }

            return this.medium.file.type.startsWith("image");
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
