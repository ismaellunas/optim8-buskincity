export default {
    props: {
        isEditMode: Boolean,
    },
    data() {
        return {
            contentClass: [],
            editModeWrapperClass: [],
        };
    },
    computed: {
        wrapperClass() {
            let wrapperClass = [];

            wrapperClass = wrapperClass.concat(this.contentClass ?? []);

            if (this.isEditMode) {
                wrapperClass = wrapperClass.concat(this.editModeWrapperClass);
            }

            return wrapperClass;
        },
    },
}
