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

    export default {
        name: 'Text',
        props: {
            id: String,
            entity: {type: Object, default: {}},
        },
        setup(props) {
            return {
                config: props.entity?.config,
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
