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
        canEdit() {
            return !isEmpty(this.isEditMode) && this.isEditMode;
        }
    }
}
