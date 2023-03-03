<template>
    <component
        :is="wrapperTag"
        class="buttons"
    >
        <biz-button-icon
            :icon="iconGrid"
            title="Gallery View"
            type="button"
            :class="{'is-primary': view === 'gallery'}"
            @click="setView('gallery')"
        />

        <biz-button-icon
            :icon="iconList"
            title="List View"
            type="button"
            :class="{'is-primary': view === 'list'}"
            @click="setView('list')"
        />
    </component>
</template>

<script>
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import { useModelWrapper } from '@/Libs/utils';
    import { grid as iconGrid, list as iconList } from '@/Libs/icon-class';

    export default {
        name: 'BizButtonsDisplayView',
        components: {
            BizButtonIcon,
        },
        props: {
            modelValue: {
                type: String,
                default: 'gallery',
                validator(value) {
                    return ['gallery', 'list'].includes(value)
                }
            },
            wrapperTag: {type: String, default: 'p'},
        },
        emits: [
            'on-view-changed',
            'update:modelValue',
        ],
        setup(props, {emit}) {
            return {
                iconGrid,
                iconList,
                view: useModelWrapper(props, emit),
            }
        },
        methods: {
            setView(view) {
                this.view = view;
                this.$emit('on-view-changed', view);
            },
        },
    };
</script>
