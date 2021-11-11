<template>
    <sdb-modal-card>
        <template #header>
            <p class="modal-card-title has-text-weight-bold">
                {{ isCreate ? 'Add' : 'Edit' }} Menu Item
            </p>
            <button
                class="delete"
                aria-label="close"
                @click.prevent="onClose()"
            />
        </template>

        <form @submit.prevent="onSubmit">
            <fieldset>
                <sdb-image
                    v-if="hasImage"
                    class="mb-2 is-48x48"
                    :src="imgUrl"
                />
                <sdb-form-file
                    v-model="form.file"
                    label="Upload Image"
                    :accepted-types="acceptedTypes"
                    required
                    :message="error('file')"
                    @on-file-picked="onFilePicked"
                />
                <sdb-form-input
                    v-model="form.url"
                    label="Link"
                    placeholder="e.g https:://example.com/"
                    required
                    :message="error('url')"
                />
            </fieldset>
        </form>
        <template #footer>
            <div
                class="columns"
                style="width: 100%"
            >
                <div
                    v-if="!isCreate"
                    class="column"
                >
                    <div class="is-pulled-left">
                        <sdb-button
                            class="is-danger"
                            @click.prevent="deleteLink(selectedIndex)"
                        >
                            <span class="icon is-small">
                                <i class="far fa-trash-alt" />
                            </span>
                        </sdb-button>
                    </div>
                </div>
                <div class="column">
                    <div class="is-pulled-right">
                        <sdb-button @click.prevent="onClose()">
                            Cancel
                        </sdb-button>
                        <sdb-button
                            class="is-primary ml-1"
                            type="button"
                            @click.prevent="onSubmit()"
                        >
                            {{ isCreate ? 'Create' : 'Update' }}
                        </sdb-button>
                    </div>
                </div>
            </div>
        </template>
    </sdb-modal-card>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import SdbButton from '@/Sdb/Button';
    import SdbFormFile from '@/Sdb/Form/File';
    import SdbFormInput from '@/Sdb/Form/Input';
    import SdbImage from '@/Sdb/Image';
    import SdbModalCard from '@/Sdb/ModalCard';
    import { isBlank } from '@/Libs/utils';
    import { cloneDeep } from 'lodash';
    import { usePage } from '@inertiajs/inertia-vue3';
    import { reactive } from 'vue';

    export default {
        name: 'NavigationFormMenu',

        components: {
            SdbButton,
            SdbFormFile,
            SdbFormInput,
            SdbImage,
            SdbModalCard,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        props: {
            socialMedia: {
                type: Object,
                default: () => {},
            },
            selectedIndex: {
                type: Number,
                default: null,
            },
        },

        emits: [
            'addSocialMedia',
            'close',
            'deleteLink',
        ],

        setup(props) {
            let fields = {};

            if (!isBlank(props.socialMedia)) {
                fields = props.socialMedia;
                fields.file = null;
            } else {
                fields = reactive({
                    id: null,
                    file: null,
                    image_url: null,
                    url: null,
                });
            }

            return {
                baseRouteName: usePage().props.value.baseRouteName ?? null,
                form: fields,
                firstFields: cloneDeep(fields),
            };
        },

        data() {
            return {
                loader: null,
                acceptedTypes: [
                    '.jpeg',
                    '.jpg',
                    '.png',
                ],
            };
        },

        computed: {
            isCreate() {
                return isBlank(this.socialMedia);
            },

            hasImage() {
                return this.form.image_url;
            },

            imgUrl() {
                return this.form.image_url ?? null;
            },
        },

        methods: {
            onSubmit() {
                if (isBlank(this.socialMedia)) {
                    this.$emit('close');
                    this.$emit('addSocialMedia', this.form);
                } else {
                    this.$emit('close');
                }
            },

            onClose() {
                this.resetForm();
                this.$emit('close');
            },

            resetForm() {
                const fields = this.firstFields;
                this.form['file'] = fields['file'];
                this.form['image_url'] = fields['image_url'];
                this.form['url'] = fields['url'];
            },

            onFilePicked(event) {
                this.form['image_url'] = event.target.result;
            },

            deleteLink(index) {
                this.$emit('deleteLink', index);
            }
        },
    }
</script>
