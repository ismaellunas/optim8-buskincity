<template>
    <form method="post" @submit.prevent="$emit('on-submit')">
        <div class="mb-5">
            <div class="columns">
                <div class="column is-half">
                    <sdb-form-input
                        label="Title"
                        v-model="form.title"
                        :message="error('title')"
                        placeholder="e.g A Good News"
                        :disabled="disableInput"
                        required
                        @on-blur="populateSlug"
                        @on-keypress="keyPressTitle"
                    />
                </div>
                <div class="column is-half">
                    <sdb-form-input-addons
                        v-model="form.slug"
                        label="Slug"
                        placeholder="e.g. a-good-news"
                        :disabled="isSlugDisabled || disableInput"
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
                </div>
            </div>
            <div class="columns">
                <div class="column is-half">
                    <sdb-form-select
                        label="Language"
                        v-model="form.locale"
                        :message="error('locale')"
                        :disabled="disableInput"
                        class="is-fullwidth"
                    >
                        <option v-for="option in localeOptions" :value="option.id" :key="option.id">
                            {{ option.id.toUpperCase() }}
                        </option>
                    </sdb-form-select>
                </div>
                <div class="column is-half">
                    <sdb-form-select
                        label="Status"
                        v-model="form.status"
                        :message="error('status')"
                        :disabled="disableInput"
                        class="is-fullwidth"
                    >
                        <option v-for="option in statusOptions" :value="option.id" :key="option.id">
                            {{ option.value }}
                        </option>
                    </sdb-form-select>
                </div>
            </div>
            <div class="field">
                <sdb-label>Category</sdb-label>

                <div class="buttons">
                    <sdb-button
                        v-for="category in sortedCategoryOptions"
                        type="button"
                        :key="category.id"
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
                :disabled="disableInput"
                rows="2"
            />
            <sdb-form-text-editor
                v-model="form.content"
                label="Content"
                :disabled="disableInput"
                :message="error('content')"
                :config="fullEditorConfig"
            />
            <sdb-form-input
                label="Meta Title"
                v-model="form.meta_title"
                :message="error('meta_title')"
                placeholder="meta title"
                :disabled="disableInput"
            />
            <sdb-form-input
                label="Meta Description"
                v-model="form.meta_description"
                :message="error('meta_description')"
                placeholder="meta description"
                :disabled="disableInput"
            />
        </div>

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
    import MixinImageLibrary from '@/Mixins/MediaLibrary';
    import SdbButton from '@/Sdb/Button';
    import SdbButtonIcon from '@/Sdb/ButtonIcon';
    import SdbButtonLink from '@/Sdb/ButtonLink';
    import SdbFormInput from '@/Sdb/Form/Input';
    import SdbFormInputAddons from '@/Sdb/Form/InputAddons';
    import SdbFormSelect from '@/Sdb/Form/Select';
    import SdbFormTextEditor from '@/Sdb/Form/TextEditor';
    import SdbFormTextarea from '@/Sdb/Form/Textarea';
    import SdbImage from '@/Sdb/Image';
    import SdbLabel from '@/Sdb/Label';
    import SdbModalImageBrowser from '@/Sdb/Modal/ImageBrowser';
    import { acceptedImageTypes } from '@/Libs/defaults';
    import { fullEditorConfig } from '@/Libs/tinymce-configs';
    import { ref } from 'vue';
    import { isEmpty, sortBy, pull } from 'lodash';
    import { useModelWrapper } from '@/Libs/utils';
    import { convertToSlug, regexSlug } from '@/Libs/utils';

    export default {
        name: 'PostForm',
        components: {
            SdbButton,
            SdbButtonIcon,
            SdbButtonLink,
            SdbFormInput,
            SdbFormInputAddons,
            SdbFormSelect,
            SdbFormTextEditor,
            SdbFormTextarea,
            SdbImage,
            SdbLabel,
            SdbModalImageBrowser,
        },
        mixins: [
            MixinHasModal,
            MixinHasPageErrors,
            MixinImageLibrary,
        ],
        emits: ['on-submit'],
        props: {
            categoryOptions: Array,
            errors: {},
            isNew: Boolean,
            localeOptions: {},
            modelValue: {},
            statusOptions: Array,
            coverImage: {type: Object, default:  {file_url: null}},
        },
        setup(props, { emit }) {
            return {
                coverSrc: ref(props.coverImage?.file_url ?? null),
                form: useModelWrapper(props, emit),
            };
        },
        data() {
            return {
                disableInput: false,
                baseRouteName: 'admin.posts',
                fullEditorConfig: {
                    height: 300,
                    menubar: false,
                    plugins: [
                        'advlist autolink lists link image charmap print preview anchor',
                        'searchreplace visualblocks code fullscreen',
                        'insertdatetime media table paste code wordcount hr code'
                    ],
                    block_formats: 'Paragraph=p; Header 1=h1; Header 2=h2; Header 3=h3',
                    toolbar: 'fullscreen | formatselect | ' +
                    'bold italic underline strikethrough blockquote | ' +
                    'forecolor backcolor | alignleft aligncenter alignright alignjustify | ' +
                    'bullist numlist outdent indent hr | anchor link table charmap code | ' +
                    'removeformat image',
                    content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
                    contextmenu: 'link image',
                    file_picker_types: 'image', //'file image media'
                    file_picker_callback : function(callback, value, meta) {
                        var x = window.innerWidth || document.documentElement.clientWidth || document.getElementsByTagName('body')[0].clientWidth;
                        var y = window.innerHeight|| document.documentElement.clientHeight|| document.getElementsByTagName('body')[0].clientHeight;

                        //var cmsURL = editor_config.path_absolute + 'laravel-filemanager?editor=' + meta.fieldname;
                        //if (meta.filetype == 'image') {
                        //    cmsURL = cmsURL + "&type=Images";
                        //} else {
                        //    cmsURL = cmsURL + "&type=Files";
                        //}

                        tinyMCE.activeEditor.windowManager.openUrl({
                            //url : cmsURL,
                            url : route('admin.media.index'),
                            title : 'Filemanager',
                            width : x * 0.8,
                            height : y * 0.8,
                            resizable : "yes",
                            close_previous : "no",
                            onMessage: (api, message) => {
                                callback(message.content);
                            }
                        });
                    }
                },
                acceptedTypes: acceptedImageTypes,
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
        },
    };
</script>
