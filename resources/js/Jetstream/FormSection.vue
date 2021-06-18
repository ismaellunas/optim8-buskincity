<template>
    <div class="box">
        <div class="columns">
            <div class="column is-one-third">
                <jet-section-title>
                    <template #title><slot name="title"></slot></template>
                    <template #description><slot name="description"></slot></template>
                </jet-section-title>
            </div>
            <div class="column is-two-thirds">
                <form @submit.prevent="$emit('submitted')">
                    <div class="px-4 py-4"
                         :class="hasActions ? '' : ''">
                        <div class="">
                            <slot name="form"></slot>
                        </div>
                    </div>

                    <div class="px-4 py-3" v-if="hasActions">
                        <slot name="actions"></slot>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
    import JetSectionTitle from './SectionTitle'

    export default {
        emits: ['submitted'],

        components: {
            JetSectionTitle,
        },

        computed: {
            hasActions() {
                return !! this.$slots.actions
            }
        }
    }
</script>
