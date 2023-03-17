<template>
    <div class="column break-long-text edit-mode-column">
        <draggable
            class="dragArea list-group"
            group="components"
            handle=".handle-content"
            item-key="id"
            :animation="300"
            :empty-insert-threshold="emptyInsertThreshold"
            :list="computedComponents"
            @change="log"
        >
            <template #item="{ element }">
                <component
                    :is="'PB'+element.componentName"
                    :id="element.id"
                    v-model="computedDataEntities[element.id]"
                    class="component-configurable"
                    :data-id="element.id"
                    :selected-locale="selectedLocale"
                    @delete-content="deleteContent"
                    @duplicate-content="duplicateContent"
                />
            </template>
        </draggable>
    </div>
</template>

<script>
    import Draggable from 'vuedraggable';
    import PBButton from '@/Blocks/Contents/Button.vue';
    import PBCard from '@/Blocks/Contents/Card.vue';
    import PBCardText from '@/Blocks/Contents/CardText.vue';
    import PBCarousel from '@/Blocks/Contents/Carousel.vue';
    import PBEventsCalendar from '@mod/Booking/Resources/assets/js/Blocks/Contents/EventsCalendar.vue';
    import PBFaq from '@/Blocks/Contents/Faq.vue';
    import PBFormBuilder from '@mod/FormBuilder/Resources/assets/js/Blocks/Contents/FormBuilder.vue';
    import PBHeading from '@/Blocks/Contents/Heading.vue';
    import PBIcon from '@/Blocks/Contents/Icon.vue';
    import PBIconText from '@/Blocks/Contents/IconText.vue';
    import PBImage from '@/Blocks/Contents/Image.vue';
    import PBLatestPost from '@/Blocks/Contents/LatestPost.vue';
    import PBTabs from '@/Blocks/Contents/Tabs.vue';
    import PBText from '@/Blocks/Contents/Text.vue';
    import PBUserList from '@/Blocks/Contents/UserList.vue';
    import PBVideo from '@/Blocks/Contents/Video.vue';
    import { isBlank, useModelWrapper, generateElementId } from '@/Libs/utils';
    import { usePage } from '@inertiajs/vue3';
    import { cloneDeep } from 'lodash';

    export default {
        name: 'PBColumn',
        components: {
            Draggable,
            PBButton,
            PBCard,
            PBCardText,
            PBCarousel,
            PBEventsCalendar,
            PBFaq,
            PBFormBuilder,
            PBHeading,
            PBIcon,
            PBIconText,
            PBImage,
            PBLatestPost,
            PBTabs,
            PBText,
            PBUserList,
            PBVideo,
        },
        props: {
            id: { type: String, required: true },
            isDebugMode: { type: Boolean, default: false },
            components: { type: Array, default: () => [] },
            dataEntities: { type: Object, default: () => {} },
            selectedLocale: { type: String, required: true },
        },
        setup(props, { emit }) {
            return {
                computedComponents: useModelWrapper(props, emit, 'components'),
                computedDataEntities: useModelWrapper(props, emit, 'dataEntities'),
                entityId: usePage().props.entityId ?? null,
            };
        },
        computed: {
            isEmptyComponents() {
                return !Array.isArray(this.computedComponents) || !this.computedComponents.length;
            },
            isDraggableEmpty() {
                return this.isEmptyComponents;
            },
            emptyInsertThreshold() {
                return this.isDraggableEmpty ? 50 : 5;
            },
        },
        methods: {
            log: function(evt) {
                if (this.isDebugMode) {
                    window.console.log(evt);
                }
            },
            cloneComponent(content) {
                let clonedContent = JSON.parse(JSON.stringify(content));
                clonedContent.id = generateElementId();
                return clonedContent;
            },
            deleteContent(id) {
                if (!isBlank(id)) {
                    this.computedComponents.splice(
                        this.computedComponents.map(block => block.id).indexOf(id),
                        1
                    );

                    delete this.computedDataEntities[id];
                }
            },
            duplicateContent(id) {
                if (!isBlank(id)) {
                    const duplicateComponent = cloneDeep(
                        this.computedComponents[
                            this.computedComponents.map(block => block.id).indexOf(id)
                        ]
                    );

                    duplicateComponent.id = generateElementId();

                    const duplicateEntity = cloneDeep(this.computedDataEntities[id]);
                    duplicateEntity.id = duplicateComponent.id;

                    this.computedDataEntities[duplicateComponent.id] = duplicateEntity;
                    this.computedComponents.push(duplicateComponent);
                }
            },
        }
    };
</script>
