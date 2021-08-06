import { isEmpty } from 'lodash';

export default {
    props: {
        isEditMode: Boolean,
    },
    data() {
        return {
            editModeWrapperClass: [],
        };
    },
    computed: {
        wrapperClass() {
            let wrapperClass = [];

            if (this.isEditMode) {
                wrapperClass = wrapperClass.concat(this.editModeWrapperClass);
            }

            return wrapperClass;
        },
        canEdit() {
            return !isEmpty(this.isEditMode) && this.isEditMode;
        }
    }
}
