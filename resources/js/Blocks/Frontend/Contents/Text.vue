<template>
    <div :class="wrapperClass">
        <div
            class="content"
            :class="contentClass"
            v-html="entity.content.html"
        >
        </div>
    </div>
</template>

<script>
    import { concat } from 'lodash';
    import { createMarginClasses, createPaddingClasses } from '@/Libs/page-builder';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'Text',
        props: {
            id: String,
            modelValue: Object,
        },
        setup(props, { emit }) {
            return {
                config: props.modelValue.config,
                entity: useModelWrapper(props, emit),
            };
        },
        computed: {
            contentClass() {
                return concat(
                    (this.config.text?.alignment ?? ''),
                    (this.config.text?.size ?? '')
                ).filter(Boolean);
            },
            wrapperClass() {
                return concat(
                    createPaddingClasses(this.config.wrapper?.padding),
                    createMarginClasses(this.config.wrapper?.margin)
                ).filter(Boolean);
            }
        }
    }
</script>
