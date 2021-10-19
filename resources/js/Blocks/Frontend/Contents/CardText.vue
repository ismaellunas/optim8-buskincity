<template>
    <div>
        <div class="card sdb-card-text">
            <div class="card-content">
                <div class="content" :class="cardContentClass">
                    <div v-html="entity.content.cardContent.content.html"></div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import { useModelWrapper } from '@/Libs/utils'

    export default {
        name: 'CardText',

        props: {
            id: String,
            modelValue: {type: Object, required: true},
        },

        setup(props, { emit }) {
            return {
                config: props.modelValue.config,
                entity: useModelWrapper(props, emit),
            };
        },

        computed: {
            cardContentClass() {
                let classes = [];
                classes.push(this.config.content?.size ?? 'is-normal');
                return classes;
            },
        }
    }
</script>
