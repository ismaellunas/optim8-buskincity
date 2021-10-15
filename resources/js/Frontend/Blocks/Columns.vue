<template>
    <div class="columns">
        <template v-for="(column, index) in block.columns">
            <block-column
                :id="column.id"
                :components="block.columns[index].components"
                :data-entities="entities"
                :data-media="media"
                :selected-locale="selectedLocale"
            />
        </template>
    </div>
</template>

<script>
    import BlockColumn from '@/Frontend/Blocks/Column';
    import SdbButton from '@/Sdb/Button';
    import SdbSelect from '@/Sdb/Select';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'BlockColumns',
        components: {
            BlockColumn,
            SdbButton,
            SdbSelect,
        },
        props: {
            dataEntities: {},
            dataMedia: {},
            id: {},
            modelValue: {},
            selectedLocale: String,
        },
        data() {
            return {
                //
            };
        },
        setup(props, { emit }) {
            return {
                block: useModelWrapper(props, emit),
                entities: useModelWrapper(props, emit, 'dataEntities'),
                media: useModelWrapper(props, emit, 'dataMedia'),
            };
        },
        methods: {
            //
        },
        computed: {
            //
        },
    };
</script>
