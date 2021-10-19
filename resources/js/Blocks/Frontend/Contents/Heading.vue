<template>
    <div>
        <component
            :is="headingTag"
            :class="headingClass"
            v-text="entity.content.heading.html"
        />
    </div>
</template>

<script>
    import { useModelWrapper } from '@/Libs/utils';
    import { last } from 'lodash';

    export default {
        name: 'Heading',

        props: {
            id: String,
            modelValue: {type: Object},
        },

        setup(props, { emit }) {
            return {
                entity: useModelWrapper(props, emit),
                config: props.modelValue.config,
            };
        },

        computed: {
            headingTag() {
                return this.config.heading?.tag ?? 'h1';
            },

            headingClass() {
                let classes = [];
                classes.push(this.config.heading?.type ?? 'title');
                classes.push('is-' + last(this.headingTag));
                classes.push(this.config.heading?.alignment ?? "");
                return classes;
            }
        }
    }
</script>
