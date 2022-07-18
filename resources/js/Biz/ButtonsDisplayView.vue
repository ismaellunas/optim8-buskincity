<template>
    <component
        :is="wrapperTag"
        :class="class"
    >
        <biz-button-icon
            :icon="icon.table"
            title="Gallery View"
            type="button"
            :class="{'is-primary': view === 'gallery'}"
            @click="setView('gallery')"
        />

        <biz-button-icon
            :icon="icon.tableList"
            title="List View"
            type="button"
            :class="{'is-primary': view === 'list'}"
            @click="setView('list')"
        />
    </component>
</template>

<script>
    import BizButtonIcon from '@/Biz/ButtonIcon';
    import { useModelWrapper } from '@/Libs/utils';
    import icon from '@/Libs/icon-class';

    export default {
        name: 'BizButtonsDisplayView',
        components: {
            BizButtonIcon,
        },
        props: {
            class: {default: 'buttons'},
            modelValue: {default: 'gallery'},
            wrapperTag: {type: String, default: 'p'},
        },
        emits: [
            'on-view-changed',
            'update:modelValue',
        ],
        setup(props, {emit}) {
            return {
                icon,
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
