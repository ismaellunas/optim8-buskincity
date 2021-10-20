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
    import { last } from 'lodash';

    export default {
        name: 'Heading',

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
