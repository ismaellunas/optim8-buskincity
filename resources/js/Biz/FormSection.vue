<template>
    <div class="box">
        <div class="columns">
            <div class="column is-one-third">
                <biz-section-title>
                    <template #title>
                        <slot name="title" />
                    </template>
                    <template #description>
                        <slot name="description" />
                    </template>
                </biz-section-title>
            </div>
            <div class="column is-two-thirds">
                <form @submit.prevent="$emit('submitted')">
                    <div
                        class="px-4 py-4"
                        :class="hasActions ? '' : ''"
                    >
                        <div class="">
                            <slot name="form" />
                        </div>
                    </div>

                    <div
                        v-if="hasActions"
                        class="px-4 py-3"
                    >
                        <slot name="actions" />
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
    import BizSectionTitle from './SectionTitle'

    export default {
        name: 'BizFormSection',

        components: {
            BizSectionTitle,
        },

        emits: ['submitted'],

        computed: {
            hasActions() {
                return !! this.$slots.actions
            }
        }
    }
</script>
