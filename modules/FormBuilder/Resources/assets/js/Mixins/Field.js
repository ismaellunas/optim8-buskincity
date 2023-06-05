import { isEmpty } from 'lodash';

export default {
    props: {
        id: { type: String, required: true },
        modelValue: { type: Object, required: true },
    },

    data() {
        return {
            value: null,
        };
    },

    computed: {
        hasNotes() {
            return ! isEmpty(this.modelValue.notes);
        },
    },
}
