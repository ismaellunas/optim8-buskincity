<template>
    <form
        class="columns"
        method="post"
        @submit.prevent="$emit('on-submit')"
    >
        <div class="column is-two-thirds">
            <fieldset :disabled="isInputDisabled" class="box">
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
                        label="Title"
                        :message="error('title')"
                        placeholder="e.g A Good News"
                        required
                        @on-blur="populateSlug"
                        @on-keypress="keyPressTitle"
                    />

                    <biz-form-slug
                        v-model="form.slug"
                        label="Slug"
                        :message="error('slug')"
                        :disabled="isInputDisabled"
                    />

                    <biz-form-select
                        v-model="form.locale"
                        class="is-fullwidth"
                        label="Language"
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
                        <biz-label>Category</biz-label>

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
                    </div>

                    <div class="field">
                        <biz-label>Thumbnail</biz-label>

                        <biz-button-icon
                            v-if="!hasCover"
                            class="is-borderless is-shadowless is-inverted"
                            icon="far fa-image"
                            type="button"
                            :disabled="!can.media.browse"
                            @click="openModal()"
                        >
                            <span>Open Media</span>
                        </biz-button-icon>
                        <div
                            v-else
                            class="columns is-mobile"
                        >
                            <div class="column is-half is-offset-one-quarter">
                                <div class="card" >
                                    <div class="card-image">
                                        <biz-image :src="coverSrc"></biz-image>
                                    </div>
                                    <footer class="card-footer">
                                        <biz-button-icon
                                            class="card-footer-item is-borderless is-shadowless is-inverted"
                                            icon="far fa-image"
                                            type="button"
                                            @click="openModal"
                                        >
                                            <span>Open Media</span>
                                        </biz-button-icon>
                                        <biz-button
                                            class="card-footer-item is-borderless is-shadowless is-inverted"
                                            type="button"
                                            @click="removeCover"
                                        >
                                            <span>Remove</span>
                                        </biz-button>
                                    </footer>
                                </div>
                            </div>
                        </div>
                    </div>

                    <biz-form-textarea
                        label="Excerpt"
                        v-model="form.excerpt"
                        :message="error('excerpt')"
                        placeholder="..."
                        rows="2"
                    />

                    <biz-form-text-editor-full
                        v-model="form.content"
                        label="Content"
                        :disabled="isInputDisabled"
                        :is-download-enabled="can.media.read"
                        :is-media-enabled="can.media.browse"
                        :is-upload-enabled="can.media.add"
                        :message="error('content')"
                    />
                </div>

                <div v-show="isTabActive('seo')">
                    <biz-form-input
                        label="Meta Title"
                        v-model="form.meta_title"
                        :message="error('meta_title')"
                        placeholder="meta title"
                    />

                    <biz-form-input
                        label="Meta Description"
                        v-model="form.meta_description"
                        :message="error('meta_description')"
                        placeholder="meta description"
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
                        Publish Options
                    </biz-tab-list>
                </biz-tab>
                <biz-form-select
                    v-model="form.status"
                    class="is-fullwidth"
                    label="Status"
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
                    label="Scheduled At"
                    required
                    :message="error('scheduled_at')"
                />
            </fieldset>

            <div class="field is-grouped is-grouped-right">
                <div class="control">
                    <biz-button-link
                        :href="route(baseRouteName+'.index')"
                        class="is-link is-light">
                        Cancel
                    </biz-button-link>
                </div>
                <div class="control">
                    <biz-button class="is-link">
                        <template v-if="isNew">Create</template>
                        <template v-else>Update</template>
                    </biz-button>
                </div>
            </div>
        </div>
    </form>

    <biz-modal-image-browser
        v-if="isModalOpen"
        :data="media"
        :is-download-enabled="can.media.read"
        :is-upload-enabled="can.media.add"
        :query-params="mediaListQueryParams"
        :search="search"
        @close="closeModal"
        @on-clicked-pagination="getMediaList"
        @on-media-selected="selectFile"
        @on-media-submitted="updateImage"
        @on-view-changed="setView"
    />
</template>

<script>
    import MixinHasModal from '@/Mixins/HasModal';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import MixinHasTab from '@/Mixins/HasTab';
    import MixinImageLibrary from '@/Mixins/MediaLibrary';
    import BizButton from '@/Biz/Button';
    import BizButtonIcon from '@/Biz/ButtonIcon';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizFormDateTime from '@/Biz/Form/DateTime';
    import BizFormInput from '@/Biz/Form/Input';
    import BizFormSlug from '@/Biz/Form/Slug';
    import BizFormSelect from '@/Biz/Form/Select';
    import BizFormTextEditorFull from '@/Biz/Form/TextEditorFull';
    import BizFormTextarea from '@/Biz/Form/Textarea';
    import BizImage from '@/Biz/Image';
    import BizLabel from '@/Biz/Label';
    import BizModalImageBrowser from '@/Biz/Modal/ImageBrowser';
    import BizTab from '@/Biz/Tab';
    import BizTabList from '@/Biz/TabList';
    import { acceptedImageTypes } from '@/Libs/defaults';
    import { convertToSlug } from '@/Libs/utils';
    import { head, isEmpty, keys, pull, sortBy } from 'lodash';
    import { ref } from 'vue';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'PostForm',
        components: {
            BizButton,
            BizButtonIcon,
            BizButtonLink,
            BizFormDateTime,
            BizFormInput,
            BizFormSlug,
            BizFormSelect,
            BizFormTextEditorFull,
            BizFormTextarea,
            BizImage,
            BizLabel,
            BizModalImageBrowser,
            BizTab,
            BizTabList,
        },
        mixins: [
            MixinHasModal,
            MixinHasPageErrors,
            MixinHasTab,
            MixinImageLibrary,
        ],
        emits: ['on-submit'],
        props: {
            can: Object,
            categoryOptions: Array,
            coverImage: {type: Object, default:  {file_url: null}},
            errors: {},
            isNew: Boolean,
            isProcessing: Boolean,
            localeOptions: {},
            modelValue: {},
            statusOptions: Array,
        },
        setup(props, { emit }) {
            return {
                coverSrc: ref(props.coverImage?.file_url ?? null),
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
                isSlugDisabled: true,
            };
        },
        methods: {
            selectCategory(category) {
                if (this.form.categories.includes(category.id)) {
                    pull(this.form.categories, category.id);
                } else {
                    this.form.categories.push(category.id);
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
                this.selectFile(response.data);
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
    };
</script>
