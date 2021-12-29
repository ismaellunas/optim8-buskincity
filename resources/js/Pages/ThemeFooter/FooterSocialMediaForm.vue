<template>
    <sdb-modal-card @close="$emit('close')">
        <template #header>
            <p class="modal-card-title has-text-weight-bold">
                {{ isCreate ? 'Add' : 'Edit' }} Social Media
            </p>
            <button
                class="delete"
                aria-label="close"
                @click.prevent="onClose()"
            />
        </template>

        <form @submit.prevent="onSubmit">
            <fieldset>
                <sdb-form-input-icon
                    v-model="form.icon"
                    label="Icon"
                    placeholder="e.g fas fa-square-full"
                    required
                    :icon-classes="iconClasses"
                    :message="error('icon', null, errors)"
                />

                <sdb-form-input
                    v-model="form.url"
                    label="Link"
                    placeholder="e.g https:://example.com/"
                    required
                    :message="error('url', null, errors)"
                />

                <sdb-checkbox
                    v-model:checked="form.is_blank"
                    :value="true"
                >
                    Open link in a new tab
                </sdb-checkbox>
            </fieldset>
        </form>
        <template #footer>
            <div
                class="columns"
                style="width: 100%"
            >
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
    import SdbCheckbox from '@/Sdb/Checkbox';
    import SdbFormInput from '@/Sdb/Form/Input';
    import SdbFormInputIcon from '@/Sdb/Form/InputIcon';
    import SdbModalCard from '@/Sdb/ModalCard';
    import fontawesomeBrandClasses from '@/Json/fontawesome-brand-classes';
    import { isBlank } from '@/Libs/utils';
    import { cloneDeep } from 'lodash';
    import { usePage } from '@inertiajs/inertia-vue3';
    import { reactive } from 'vue';

    export default {
        name: 'FooterSocialMediaForm',

        components: {
            SdbButton,
            SdbCheckbox,
            SdbFormInput,
            SdbFormInputIcon,
            SdbModalCard,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        props: {
            errors: {
                type: Object,
                default: () => {},
            },
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
            'add-social-media',
            'close',
            'update-social-media',
        ],

        setup(props) {
            let fields = {};

            if (!isBlank(props.socialMedia)) {
                fields = props.socialMedia;
            } else {
                fields = {
                    id: null,
                    url: null,
                    icon: null,
                    is_blank: false,
                };
            }

            return {
                baseRouteName: usePage().props.value.baseRouteName ?? null,
                form: reactive(fields),
                firstFields: cloneDeep(fields),
                iconClasses: fontawesomeBrandClasses,
            };
        },

        computed: {
            isCreate() {
                return isBlank(this.socialMedia);
            },
        },

        methods: {
            onSubmit() {
                if (isBlank(this.socialMedia)) {
                    this.$emit('add-social-media', this.form);
                } else {
                    this.$emit('update-social-media', this.form);
                }
            },

            onClose() {
                this.resetForm();
                this.$emit('close');
            },

            resetForm() {
                const fields = this.firstFields;
                this.form['url'] = fields['url'];
                this.form['icon'] = fields['icon'];
                this.form['is_blank'] = fields['is_blank'];
            },
        },
    }
</script>
