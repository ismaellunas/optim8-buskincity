import { concat } from 'lodash';
import { createMarginStyles, createPaddingStyles } from '@/Libs/page-builder';

export default {
    props: [
        'modelValue',
    ],

    computed: {
        configDimension() {
            return this.modelValue?.config?.dimension ?? null;
        },

        dimensionStyle() {
            return concat(
                createMarginStyles(this.configDimension),
                createPaddingStyles(this.configDimension)
            );
        }
    }
}
