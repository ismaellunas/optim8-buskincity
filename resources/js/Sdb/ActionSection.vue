<template>
    <div class="box">
        <div class="columns">
            <div class="column is-one-third">
                <sdb-section-title>
                    <template #title>
                        <slot name="title" />
                    </template>
                    <template #description>
                        <slot name="description" />
                    </template>
                </sdb-section-title>
            </div>
            <div class="column is-two-thirds">
                <div class="px-4 py-4">
                    <slot name="content" />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import SdbSectionTitle from './SectionTitle'

    export default {
        name: 'SdbActionSection',

        components: {
            SdbSectionTitle,
        }
    }
</script>
