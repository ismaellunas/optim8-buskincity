import { concat } from 'lodash';
import { createMarginStyles, createPaddingStyles } from '@/Libs/page-builder';

export default {
    props: [
        'modelValue',
    ],

    setup(props) {
        return {
            config: props.modelValue?.config,
        };
    },

    computed: {
        configDimension() {
            return this.config?.dimension ?? null;
        },

        dimensionStyle() {
            return concat(
                createMarginStyles(this.configDimension),
                createPaddingStyles(this.configDimension)
            );
        }
    }
}
