<template>
    <component
        :is="wrapperTag"
        :class="class"
    >
        <sdb-button-icon
            icon="fas fa-th"
            title="Gallery View"
            type="button"
            :class="{'is-primary': view === 'gallery'}"
            @click="setView('gallery')"
        />

        <sdb-button-icon
            icon="fas fa-th-list"
            title="List View"
            type="button"
            :class="{'is-primary': view === 'list'}"
            @click="setView('list')"
        />
    </component>
</template>

<script>
    import SdbButtonIcon from '@/Sdb/ButtonIcon';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'SdbButtonsDisplayView',
        components: {
            SdbButtonIcon,
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
