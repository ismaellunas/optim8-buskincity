import { isEmpty } from 'lodash';

export default {
    props: {
        columns: { type: Object, default: () => {} },
        data: { type: Object, default: null },
    },

    computed: {
        i18n() {
            return this.data.i18n;
        },

        columnClasses() {
            if (isEmpty(this.columns)) {
                return [];
            }

            return [
                `is-${this.columns['desktop']}-desktop`,
                `is-${this.columns['tablet']}-tablet`,
                `is-${this.columns['mobile']}-mobile`,
            ];
        },
    }
};
