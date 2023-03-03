<template>
    <component
        :is="tag"
        class="is-clickable"
    >
        <biz-icon-text
            :icon="sortIcon"
            icon-position="right"
        >
            <slot />
        </biz-icon-text>
    </component>
</template>

<script>
    import BizIconText from '@/Biz/IconText.vue';
    import icon from '@/Libs/icon-class';

    export default {
        name: 'BizTableColumnSort',

        components: {
            BizIconText,
        },

        props: {
            tag: { type: String, default: 'th' },
            isSorted: { type: Boolean, default: false },
            order: {
                type: [String, null],
                default: 'asc',
                validator(value) {
                    return ['asc', 'desc'].includes(value);
                }
            },
        },

        data() {
            return {
                icon,
            };
        },

        computed: {
            sortIcon() {
                if (this.isSorted) {
                    return this.order == 'asc'
                        ? this.icon.sortUp
                        : this.icon.sortDown
                }

                return this.icon.sort;
            },
        },
    };
</script>
