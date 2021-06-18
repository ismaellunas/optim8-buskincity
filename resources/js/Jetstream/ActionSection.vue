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
                <div class="px-4 py-4">
                    <slot name="content"></slot>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import JetSectionTitle from './SectionTitle'

    export default {
        components: {
            JetSectionTitle,
        }
    }
</script>
