<template>
    <div>
        <biz-error-notifications
            :errors="$page.props.errors"
        />

        <div class="box mb-6">
            <form
                method="post"
                @submit.prevent="onSubmit"
            >
                <div class="columns">
                    <div class="column">
                        <div class="field is-grouped is-grouped-right">
                            <div class="control">
                                <biz-button class="is-link">
                                    {{ i18n.save }}
                                </biz-button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="columns is-multiline">
                    <div class="column is-12">
                        <h5 class="title is-size-5">
                            {{ startCase(i18n.default_image) }}
                        </h5>
                    </div>

                    <div class="column is-6">
                        <b>{{ i18n.post_thumbnail }}</b>
                    </div>

                    <div class="column is-6">
                        <biz-form-media-library
                            v-model="form.post_thumbnail"
                            image-preview-size="6"
                            :placeholder="i18n.open_media_library"
                            :is-download-enabled="can?.media?.read ?? false"
                            :is-image-preview-thumbnail="false"
                            :is-upload-enabled="can?.media?.add ?? false"
                            :medium="postThumbnailMedia"
                            :instructions="instructions.postThumbnailMediaLibrary"
                            :message="error('post_thumbnail')"
                        />
                    </div>

                    <div class="column is-6">
                        <b>{{ i18n.open_graph }}</b>
                    </div>

                    <div class="column is-6">
                        <biz-form-media-library
                            v-model="form.open_graph"
                            image-preview-size="6"
                            :placeholder="i18n.open_media_library"
                            :is-download-enabled="can?.media?.read ?? false"
                            :is-image-preview-thumbnail="false"
                            :is-upload-enabled="can?.media?.add ?? false"
                            :medium="openGraphMedia"
                            :instructions="instructions.openGraphMediaLibrary"
                            :message="error('open_graph')"
                        />
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import MixinHasLoader from '@/Mixins/HasLoader';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import BizButton from '@/Biz/Button.vue';
    import BizErrorNotifications from '@/Biz/ErrorNotifications.vue';
    import BizFormMediaLibrary from '@/Biz/Form/MediaLibrary.vue';;
    import { startCase } from 'lodash';
    import { acceptedImageTypes } from '@/Libs/defaults';
    import { success as successAlert } from '@/Libs/alert';
    import { useForm } from '@inertiajs/vue3';

    export default {
        name: 'ThemeOptionAdvance',

        components: {
            BizButton,
            BizErrorNotifications,
            BizFormMediaLibrary,
        },

        mixins: [
            MixinHasPageErrors,
            MixinHasLoader,
        ],

        layout: AppLayout,

        props: {
            baseRouteName: { type: String, required: true },
            can: { type: Object, default: () => {} },
            errors: { type: Object, default: () => {} },
            instructions: { type: Object, default: () => {} },
            postThumbnailMedia: { type: Object, default: () => {} },
            openGraphMedia: { type: Object, default: () => {} },
            title: { type: String, required: true },
            i18n: { type: Object, default: () => ({
                save : 'Save',
                default_image: 'Default image',
                post_thumbnail: 'Post thumbnail',
                open_graph: 'Open graph',
                open_media_library : 'Open media library',
            }) }
        },

        setup(props) {
            return {
                form: useForm({
                    post_thumbnail: props.postThumbnailMedia?.id ?? null,
                    open_graph: props.openGraphMedia?.id ?? null,
                }),
            };
        },

        data() {
            return {
                imageTypes: acceptedImageTypes,
            };
        },

        methods: {
            onSubmit() {
                const self = this;

                self.form.post(route(self.baseRouteName+'.update'), {
                    preserveScroll: false,
                    onStart: () => {
                        self.onStartLoadingOverlay();
                    },
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                    },
                    onFinish: () => {
                        self.onEndLoadingOverlay();
                    }
                });
            },

            startCase,
        },
    };
</script>
