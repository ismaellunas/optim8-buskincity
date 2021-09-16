<template>
    <form
        class="columns"
        method="post"
        @submit.prevent="$emit('on-submit')"
    >
        <div class="column is-two-thirds">
            <fieldset :disabled="isProcessing" class="box">

                <sdb-tab class="is-boxed">
                    <ul>
                        <sdb-tab-list
                            v-for="(tab, index) in tabs"
                            :key="index"
                            :is-active="isTabActive(index)"
                        >
                            <a @click.prevent="setActiveTab(index)">
                                {{ tab.title }}
                            </a>
                        </sdb-tab-list>
                    </ul>
                </sdb-tab>

                <div v-show="isTabActive('content')">
                    <sdb-form-input
                        label="Title"
                        v-model="form.title"
                        :message="error('title')"
                        placeholder="e.g A Good News"
                        :disabled="isInputDisabled"
                        required
                        @on-blur="populateSlug"
                        @on-keypress="keyPressTitle"
                    />

                    <sdb-form-input-addons
                        v-model="form.slug"
                        label="Slug"
                        placeholder="e.g. a-good-news"
                        :disabled="isSlugDisabled || isInputDisabled"
                        :message="error('slug')"
                        @on-keypress="keyPressSlug"
                    >
                        <template v-slot:afterInput>
                            <div class="control">
                                <sdb-button-icon
                                    v-show="isSlugDisabled"
                                    icon="fas fa-pen"
                                    type="button"
                                    @click="isSlugDisabled = false"
                                />
                                <sdb-button-icon
                                    v-show="!isSlugDisabled"
                                    icon="fas fa-ban"
                                    type="button"
                                    @click="isSlugDisabled = true"
                                />
                            </div>
                        </template>
                    </sdb-form-input-addons>

                    <sdb-form-select
                        v-model="form.locale"
                        class="is-fullwidth"
                        label="Language"
                        :disabled="isInputDisabled"
                        :message="error('locale')"
                    >
                        <option
                            v-for="option in localeOptions"
                            :key="option.id"
                            :value="option.id"
                        >
                            {{ option.id.toUpperCase() }}
                        </option>
                    </sdb-form-select>

                    <div class="field">
                        <sdb-label>Category</sdb-label>

                        <div class="buttons">
                            <sdb-button
                                v-for="category in sortedCategoryOptions"
                                :key="category.id"
                                type="button"
                                :class="{'is-primary': form.categories.includes(category.id)}"
                                @click="selectCategory(category)"
                            >
                                {{ category.value }}
                            </sdb-button>
                        </div>
                    </div>

                    <div class="field">
                        <sdb-label>Thumbnail</sdb-label>

                        <sdb-button-icon
                            v-if="!hasCover"
                            class="is-borderless is-shadowless is-inverted"
                            icon="far fa-image"
                            type="button"
                            @click="openModal()"
                        >
                            <span>Open Media</span>
                        </sdb-button-icon>
                        <div
                            v-else
                            class="columns is-mobile"
                        >
                            <div class="column is-half is-offset-one-quarter">
                                <div class="card" >
                                    <div class="card-image">
                                        <sdb-image :src="coverSrc"></sdb-image>
                                    </div>
                                    <footer class="card-footer">
                                        <sdb-button-icon
                                            class="card-footer-item is-borderless is-shadowless is-inverted"
                                            icon="far fa-image"
                                            type="button"
                                            @click="openModal"
                                        >
                                            <span>Open Media</span>
                                        </sdb-button-icon>
                                        <sdb-button
                                            class="card-footer-item is-borderless is-shadowless is-inverted"
                                            type="button"
                                            @click="removeCover"
                                        >
                                            <span>Remove</span>
                                        </sdb-button>
                                    </footer>
                                </div>
                            </div>
                        </div>
                    </div>

                    <sdb-form-textarea
                        label="Excerpt"
                        v-model="form.excerpt"
                        :message="error('excerpt')"
                        placeholder="..."
                        :disabled="isInputDisabled"
                        rows="2"
                    />

                    <sdb-form-text-editor-full
                        v-model="form.content"
                        label="Content"
                        :disabled="isInputDisabled"
                        :message="error('content')"
                    />
                </div>

                <div v-show="isTabActive('seo')">
                    <sdb-form-input
                        label="Meta Title"
                        v-model="form.meta_title"
                        :message="error('meta_title')"
                        placeholder="meta title"
                        :disabled="isInputDisabled"
                    />

                    <sdb-form-input
                        label="Meta Description"
                        v-model="form.meta_description"
                        :message="error('meta_description')"
                        placeholder="meta description"
                        :disabled="isInputDisabled"
                    />
                </div>
            </fieldset>
        </div>

        <div class="column">
            <fieldset
                class="box"
                :disabled="isProcessing"
            >
                <sdb-tab>
                    <sdb-tab-list>
                        Publish Options
                    </sdb-tab-list>
                </sdb-tab>
                <sdb-form-select
                    v-model="form.status"
                    class="is-fullwidth"
                    label="Status"
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
                </sdb-form-select>

                <div
                    v-if="form.status === 2"
                    class="field"
                >
                    <sdb-label>Scheduled At</sdb-label>

                    <sdb-date-time
                        v-model="form.scheduled_at"
                    />
                </div>
            </fieldset>

            <div class="field is-grouped is-grouped-right">
                <div class="control">
                    <sdb-button-link
                        :href="route(baseRouteName+'.index')"
                        class="is-link is-light">
                        Cancel
                    </sdb-button-link>
                </div>
                <div class="control">
                    <sdb-button class="is-link">
                        <template v-if="isNew">Create</template>
                        <template v-else>Update</template>
                    </sdb-button>
                </div>
            </div>
        </div>
    </form>

    <sdb-modal-image-browser
        v-if="isModalOpen"
        :data="media"
        :query-params="imageListQueryParams"
        :search="search"
        @close="closeModal"
        @on-clicked-pagination="getImagesList"
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
    import SdbButton from '@/Sdb/Button';
    import SdbButtonIcon from '@/Sdb/ButtonIcon';
    import SdbButtonLink from '@/Sdb/ButtonLink';
    import SdbDateTime from '@/Sdb/DateTime';
    import SdbFormInput from '@/Sdb/Form/Input';
    import SdbFormInputAddons from '@/Sdb/Form/InputAddons';
    import SdbFormSelect from '@/Sdb/Form/Select';
    import SdbFormTextEditorFull from '@/Sdb/Form/TextEditorFull';
    import SdbFormTextarea from '@/Sdb/Form/Textarea';
    import SdbImage from '@/Sdb/Image';
    import SdbLabel from '@/Sdb/Label';
    import SdbModalImageBrowser from '@/Sdb/Modal/ImageBrowser';
    import SdbTab from '@/Sdb/Tab';
    import SdbTabList from '@/Sdb/TabList';
    import { acceptedImageTypes } from '@/Libs/defaults';
    import { convertToSlug, regexSlug } from '@/Libs/utils';
    import { head, isEmpty, keys, pull, sortBy } from 'lodash';
    import { ref } from 'vue';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'PostForm',
        components: {
            SdbButton,
            SdbButtonIcon,
            SdbButtonLink,
            SdbDateTime,
            SdbFormInput,
            SdbFormInputAddons,
            SdbFormSelect,
            SdbFormTextEditorFull,
            SdbFormTextarea,
            SdbImage,
            SdbLabel,
            SdbModalImageBrowser,
            SdbTab,
            SdbTabList,
        },
        mixins: [
            MixinHasModal,
            MixinHasPageErrors,
            MixinHasTab,
            MixinImageLibrary,
        ],
        emits: ['on-submit'],
        props: {
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
                this.getImagesList(route(this.imageListRouteName));
            },
            onImageSelected() { /* @override Mixins/ContainImageContent */
                this.closeModal();
                this.isFormOpen = false;
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
            keyPressSlug(event) {
                // @see https://stackoverflow.com/questions/61938667/vue-js-how-to-allow-an-user-to-type-only-letters-in-an-input-field
                let char = String.fromCharCode(event.keyCode);
                const lastCharacter = event.target.value.slice(-1);

                if ( (char === ' ' || char === '_') && (lastCharacter !== '-')) {
                    event.target.value += '-';
                } else if (char === '-' && lastCharacter === '-') {
                    event.target.value += '';
                } else if ((new RegExp('^['+regexSlug+']+$')).test(char)) {
                    return true;
                }
                event.preventDefault();
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
