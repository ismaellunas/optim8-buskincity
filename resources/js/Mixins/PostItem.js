import { isEmpty } from 'lodash';

export default {
    props: {
        record: Object,
    },
    computed: {
        hasThumbnail() {
            return !isEmpty(this.record.thumbnail_url);
        },

        hasCategory() {
            if (this.record?.categories) {
                return this.record.categories.length > 0;
            }
            return false;
        },
    }
};
