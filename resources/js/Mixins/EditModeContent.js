import EditModeComponentMixin from './EditModeComponent';

export default {
    mixins: [EditModeComponentMixin],
    data() {
        return {
            editModeWrapperClass: ['edit-mode-content'],
        };
    },
    computed: {
        canEdit() {
            return this.isEditMode;
        }
    }
}
