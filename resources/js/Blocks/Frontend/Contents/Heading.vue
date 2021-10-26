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
    import { last, concat } from 'lodash';

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
                return concat(
                    this.config.heading?.type ?? "title",
                    'is-' + last(this.headingTag),
                    this.config.heading?.alignment ?? "",
                ).filter(Boolean);
            }
        }
    }
</script>
