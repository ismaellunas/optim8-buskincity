<template>
    <div>
        <h2 class="title is-4 mt-5">
            <slot name="title" />
        </h2>
        <div class="box is-shadowless">
            <form @submit.prevent="$emit('submitted')">
                <slot name="form" />

                <div v-if="hasActions">
                    <slot name="actions" />
                </div>
            </form>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'FrontendFormSection',

        emits: ['submitted'],

        computed: {
            hasActions() {
                return !! this.$slots.actions
            }
        }
    };
</script>
