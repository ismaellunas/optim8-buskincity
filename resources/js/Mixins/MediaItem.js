export default {
    computed: {
        isImage() {
            return (
                (this.medium?.is_image)
                || (this.medium?.file && this.medium.file.type.startsWith("image"))
            );
        },
        thumbnailIcon() {
            if (this.medium.file_type === "video") {
                return "far fa-file-video";
            } else if (this.medium.extension) {
                if (this.medium.extension === "pdf") {
                    return "far fa-file-pdf";
                } else if (this.medium.extension.startsWith('doc')) {
                    return "far fa-file-word";
                } else if (this.medium.extension.startsWith('ppt')) {
                    return "far fa-file-powerpoint";
                } else if (this.medium.extension.startsWith('xls')) {
                    return "far fa-file-excel";
                }
            }
            return "far fa-file-alt";
        }
    },
};
