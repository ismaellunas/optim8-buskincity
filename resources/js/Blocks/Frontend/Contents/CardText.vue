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
    export default {
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
            cardContentClass() {
                let classes = [];
                classes.push(this.config.content?.size ?? 'is-normal');
                return classes;
            },
        }
    }
</script>
