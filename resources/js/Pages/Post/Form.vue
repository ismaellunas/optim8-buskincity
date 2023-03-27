<template>
    <div>
        <form
            class="columns"
            method="post"
            @submit.prevent="$emit('on-submit')"
        >
            <div class="column is-two-thirds">
                <fieldset
                    class="box"
                    :disabled="isInputDisabled"
                >
                    <biz-tab class="is-boxed">
                        <ul>
                            <biz-tab-list
                                v-for="(tab, index) in tabs"
                                :key="index"
                                :is-active="isTabActive(index)"
                            >
                                <a @click.prevent="setActiveTab(index)">
                                    {{ tab.title }}
                                </a>
                            </biz-tab-list>
                        </ul>
                    </biz-tab>

                    <div v-show="isTabActive('content')">
                        <biz-form-input
                            v-model="form.title"
                            :label="i18n.title"
                            :message="error('title')"
                            placeholder="e.g A Good News"
                            required
                            @on-blur="populateSlug"
                            @on-keypress="keyPressTitle"
                        />

                        <biz-form-slug
                            v-model="form.slug"
                            :label="i18n.slug"
                            :message="error('slug')"
                            :disabled="isInputDisabled"
                        />

                        <biz-form-select
                            v-model="form.locale"
                            class="is-fullwidth"
                            :label="i18n.language"
                            required
                            :message="error('locale')"
                        >
                            <option
                                v-for="option in localeOptions"
                                :key="option.id"
                                :value="option.id"
                            >
                                {{ option.id.toUpperCase() }}
                            </option>
                        </biz-form-select>

                        <div class="field">
                            <biz-label>
                                {{ i18n.category }}
                            </biz-label>

                            <div class="buttons">
                                <biz-button
                                    v-for="category in sortedCategoryOptions"
                                    :key="category.id"
                                    type="button"
                                    :class="{'is-primary': form.categories.includes(category.id)}"
                                    @click="selectCategory(category)"
                                >
                                    {{ category.value }}
                                </biz-button>
                            </div>

                            <p>
                                {{ i18n.select_primary_category }}
                            </p>
                            <biz-form-select
                                v-model="form.primary_category"
                                class="is-fullwidth"
                                placeholder="- Select -"
                                :message="error('primary_category')"
                            >
                                <template
                                    v-for="category in sortedCategoryOptions"
                                    :key="category.id"
                                >
                                    <option
                                        v-if="form.categories.includes(category.id)"
                                        :value="category.id"
                                    >
                                        {{ category.value }}
                                    </option>
                                </template>
                            </biz-form-select>
                        </div>

                        <div class="field">
                            <biz-label>
                                {{ i18n.thumbnail }}
                            </biz-label>

                            <biz-button-icon
                                v-if="!hasCover"
                                class="is-borderless is-shadowless is-inverted"
                                :icon="icon.image"
                                type="button"
                                :disabled="!can.media.browse"
                                @click="openModal()"
                            >
                                <span>
                                    {{ i18n.open_media }}
                                </span>
                            </biz-button-icon>
                            <div
                                v-else
                                class="columns is-mobile"
                            >
                                <div class="column is-half is-offset-one-quarter">
                                    <div class="card">
                                        <div class="card-image">
                                            <biz-image :src="coverSrc" />
                                        </div>
                                        <footer class="card-footer">
                                            <biz-button-icon
                                                class="card-footer-item is-borderless is-shadowless is-inverted"
                                                :icon="icon.image"
                                                type="button"
                                                @click="openModal"
                                            >
                                                <span>
                                                    {{ i18n.open_media }}
                                                </span>
                                            </biz-button-icon>
                                            <biz-button
                                                class="card-footer-item is-borderless is-shadowless is-inverted"
                                                type="button"
                                                @click="removeCover"
                                            >
                                                <span>
                                                    {{ i18n.remove }}
                                                </span>
                                            </biz-button>
                                        </footer>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <biz-form-textarea
                            v-model="form.excerpt"
                            :label="i18n.excerpt"
                            placeholder="..."
                            rows="2"
                            :message="error('excerpt')"
                        />

                        <biz-form-text-editor-full
                            v-model="form.content"
                            :label="i18n.content"
                            :config="editorConfig"
                            :disabled="isInputDisabled"
                            :is-download-enabled="can.media.read"
                            :is-media-enabled="can.media.browse"
                            :is-upload-enabled="can.media.add"
                            :is-config-combined="true"
                            :message="error('content')"
                        />
                    </div>

                    <div v-show="isTabActive('seo')">
                        <biz-form-input
                            v-model="form.meta_title"
                            :label="i18n.meta_title"
                            :placeholder="i18n.meta_title"
                            :maxlength="maxLength.meta_title"
                            :message="error('meta_title')"
                        />

                        <biz-form-textarea
                            v-model="form.meta_description"
                            :label="i18n.meta_description"
                            :placeholder="i18n.meta_description"
                            rows="2"
                            :maxlength="maxLength.meta_description"
                            :message="error('meta_description')"
                        />
                    </div>
                </fieldset>
            </div>

            <div class="column">
                <fieldset
                    class="box"
                    :disabled="isInputDisabled"
                >
                    <biz-tab>
                        <biz-tab-list>
                            {{ i18n.publish_options }}
                        </biz-tab-list>
                    </biz-tab>
                    <biz-form-select
                        v-model="form.status"
                        class="is-fullwidth"
                        :label="i18n.status"
                        required
                        :disabled="isInputDisabled"
                        :message="error('status')"
                    >
                        <option
                            v-for="option in statusOptions"
                            :key="option.id"
                            :value="option.id"
                        >
                            {{ option.value }}
                        </option>
                    </biz-form-select>

                    <biz-form-date-time
                        v-if="form.status === 2"
                        v-model="form.scheduled_at"
                        :label="i18n.scheduled_at"
                        required
                        :message="error('scheduled_at')"
                    />
                </fieldset>

                <div class="field is-grouped is-grouped-right">
                    <div class="control">
                        <biz-button-link
                            :href="route(baseRouteName+'.index')"
                            class="is-link is-light"
                        >
                            {{ i18n.cancel }}
                        </biz-button-link>
                    </div>
                    <div class="control">
                        <biz-button class="is-link">
                            <template v-if="isNew">
                                {{ i18n.create }}
                            </template>
                            <template v-else>
                                {{ i18n.update }}
                            </template>
                        </biz-button>
                    </div>
                </div>
            </div>
        </form>

        <biz-modal-media-browser
            v-if="isModalOpen"
            :data="media"
            :is-download-enabled="can.media.read"
            :is-upload-enabled="can.media.add"
            :query-params="mediaListQueryParams"
            :search="search"
            :instructions="instructions.mediaLibrary"
            @close="closeModal"
            @on-clicked-pagination="getMediaList"
            @on-media-selected="selectFile"
            @on-media-submitted="updateImage"
            @on-view-changed="setView"
        />
    </div>
</template>

<script>
    import MixinHasModal from '@/Mixins/HasModal';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import MixinHasTab from '@/Mixins/HasTab';
    import MixinImageLibrary from '@/Mixins/MediaLibrary';
    import BizButton from '@/Biz/Button.vue';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import BizFormDateTime from '@/Biz/Form/DateTime.vue';
    import BizFormInput from '@/Biz/Form/Input.vue';
    import BizFormSelect from '@/Biz/Form/Select.vue';
    import BizFormSlug from '@/Biz/Form/Slug.vue';
    import BizFormTextEditorFull from '@/Biz/Form/TextEditorFull.vue';
    import BizFormTextarea from '@/Biz/Form/Textarea.vue';
    import BizImage from '@/Biz/Image.vue';
    import BizLabel from '@/Biz/Label.vue';
    import BizModalMediaBrowser from '@/Biz/Modal/MediaBrowser.vue';
    import BizTab from '@/Biz/Tab.vue';
    import BizTabList from '@/Biz/TabList.vue';
    import icon from '@/Libs/icon-class';
    import { acceptedImageTypes } from '@/Libs/defaults';
    import { convertToSlug } from '@/Libs/utils';
    import { fullConfig } from '@/Libs/tinymce-configs';
    import { head, isEmpty, keys, pull, sortBy } from 'lodash';
    import { ref } from 'vue';
    import { useModelWrapper } from '@/Libs/utils';
    import { usePage } from '@inertiajs/vue3';

    export default {
        name: 'PostForm',

        components: {
            BizButton,
            BizButtonIcon,
            BizButtonLink,
            BizFormDateTime,
            BizFormInput,
            BizFormSelect,
            BizFormSlug,
            BizFormTextEditorFull,
            BizFormTextarea,
            BizImage,
            BizLabel,
            BizModalMediaBrowser,
            BizTab,
            BizTabList,
        },

        mixins: [
            MixinHasModal,
            MixinHasPageErrors,
            MixinHasTab,
            MixinImageLibrary,
        ],

        inject: {
            i18n: {
                default: () => ({
                    content : 'Content',
                    seo : 'SEO',
                    title : 'Title',
                    slug : 'Slug',
                    language : 'Language',
                    category : 'Category',
                    select_primary_category : 'Select The Primary Category',
                    thumbnail : 'Thumbnail',
                    excerpt : 'Excerpt',
                    status : 'Status',
                    publish_options : 'Publish Options',
                    scheduled_at : 'Scheduled at',
                    open_media : 'Open Media',
                    remove : 'Remove',
                    meta_title : 'Meta Title',
                    meta_description : 'Meta Description',
                    create : 'Create',
                    update : 'Update',
                    cancel: 'Cancel',
                })
            },
        },

        props: {
            can: { type: Object, required: true },
            categoryOptions: { type: Array, default: () => [] },
            coverImage: {type: Object, default: () => {file_url: null}},
            errors: {},
            isNew: Boolean,
            isProcessing: Boolean,
            localeOptions: { type: Array, default: () => [] },
            modelValue: { type: Object, required: true },
            modules: { type: Object, default: () => {} },
            statusOptions: { type: Array, default: () => [] },
            instructions: { type: Object, default: () => {} },
        },

        emits: ['on-submit'],

        setup(props, { emit }) {
            const editorConfig = {
                toolbar2: fullConfig.toolbar2 + ' | formLists',
                setup: (editor) => {
                    if (props.modules?.form_builder) {
                        editor.ui.registry.addMenuButton('formLists', {
                            text: 'Form Lists',
                            fetch: (callback) => {
                                let items = [];
                                let formOptions = props.modules?.form_builder?.formOptions;

                                formOptions.forEach(function (option) {
                                    items.push({
                                        type: 'menuitem',
                                        text: option.name,
                                        onAction: () => editor.insertContent(
                                            '[form-builder form-id="'+ option.value + '"]'
                                        ),
                                    })
                                });

                                callback(items);
                            }
                        });
                    }
                }
            };

            return {
                coverSrc: ref(props.coverImage?.file_url ?? null),
                editorConfig,
                form: useModelWrapper(props, emit),
                tabs: {
                    content: { title: "Content"},
                    seo: { title: "SEO"},
                },
            };
        },

        data() {
            return {
                acceptedTypes: acceptedImageTypes,
                activeTab: head(keys(this.tabs)),
                baseRouteName: 'admin.posts',
                icon,
                isSlugDisabled: true,
                maxLength: usePage().props.maxLength,
            };
        },

        computed: {
            sortedCategoryOptions() {
                return sortBy(this.categoryOptions, [
                    function (category) { return category.value.toLowerCase() }
                ]);
            },
            hasCover() {
                return !isEmpty(this.coverSrc);
            },
            isInputDisabled() {
                return this.isProcessing;
            },
        },

        methods: {
            selectCategory(category) {
                if (this.form.categories.includes(category.id)) {
                    pull(this.form.categories, category.id);
                } else {
                    this.form.categories.push(category.id);
                }

                if (isEmpty(this.form.categories)) {
                    this.form.primary_category = null;
                }
            },
            onShownModal() { /* @override */
                this.setTerm('');
                this.getMediaList(route(this.mediaListRouteName));
            },
            selectFile(file) {
                this.form.cover_image_id = file.id;
                this.coverSrc = file.file_url;
                this.closeModal();
            },
            removeCover() {
                this.coverSrc = null;
                this.form.cover_image_id = null;
            },
            updateImage(response) {
                this.selectFile(response.data[0]);
                this.closeModal();
            },
            populateSlug(event) {
                if (isEmpty(this.form.slug)) {
                    this.form.slug = convertToSlug(this.form.title);
                }
            },
            keyPressTitle(event) {
                if (event.keyCode == 13) {
                    this.populateSlug(event);
                }
                return true;
            },
        },
    };
</script>
