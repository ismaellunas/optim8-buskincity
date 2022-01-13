<template>
    <div>
        <biz-toolbar-content
            @delete-content="deleteContent"
        />

        <a
            class="button"
            :class="buttonClass"
        >
            <span
                v-if="config.button.iconPosition === 'left' || config.button.iconPosition === null"
                class="icon"
                @click="openModal()"
            >
                <template v-if="entity.content.button.icon !== null">
                    <i :class="entity.content.button.icon" />
                </template>
                <template v-else>
                    <i class="empty-icon" />
                </template>
            </span>

            <span
                class="input-area"
                contenteditable
                @blur="onEditText($event)"
                v-text="entity.content.button.text"
            />

            <span
                v-if="config.button.iconPosition === 'right'"
                class="icon"
                @click="openModal()"
            >
                <template v-if="entity.content.button.icon !== null">
                    <i :class="entity.content.button.icon" />
                </template>
                <template v-else>
                    <i class="empty-icon" />
                </template>
            </span>
        </a>

        <biz-icon-browser
            v-if="isModalOpen"
            :can-remove="true"
            :has-type="true"
            :icon-classes="iconClasses"
            @close="closeModal()"
            @on-selected-icon="onSelectedIcon"
            @remove-icon="removeIcon"
        />
    </div>
</template>

<script>
    import DeletableContentMixin from '@/Mixins/DeletableContent';
    import MixinHasModal from '@/Mixins/HasModal';
    import fontawesomeAllClasses from '@/Json/fontawesome-all-classes';
    import BizIconBrowser from '@/Biz/Modal/IconBrowser';
    import BizToolbarContent from '@/Blocks/Contents/ToolbarContent';
    import { concat } from 'lodash';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: "Button",

        components: {
            BizIconBrowser,
            BizToolbarContent,
        },

        mixins: [
            DeletableContentMixin,
            MixinHasModal,
        ],

        props: {
            id: {type: String, default: null},
            modelValue: {type: Object, required: true},
        },

        setup(props, { emit }) {
            return {
                config: props.modelValue.config,
                entity: useModelWrapper(props, emit),
                iconClasses: fontawesomeAllClasses,
            };
        },

        computed: {
            buttonClass() {
                return concat(
                    (this.config.button.color ?? ''),
                    (this.config.button.isLight ? 'is-light' : ''),
                    (this.config.button.size ?? ''),
                    (this.config.button.width ?? ''),
                    (this.config.button.style ?? ''),
                ).filter(Boolean);
            }
        },

        methods: {
            onEditText(evt) {
                this.entity.content.button.text = evt.target.innerText;
            },

            onSelectedIcon(icon) {
                this.entity.content.button.icon = icon;
            },

            removeIcon() {
                this.entity.content.button.icon = null;
            }
        },
    }
</script>

<style scoped>
    .input-area {
        min-width: 20px;
        border-bottom: 1px solid #D3D3D3;
    }

    .empty-icon {
        width: 15px;
        height: 15px;
        background: #D3D3D3;
        border-radius: 50%;
    }
</style>