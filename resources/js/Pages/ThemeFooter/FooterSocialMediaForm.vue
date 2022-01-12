<template>
    <biz-modal-card @close="$emit('close')">
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
                <biz-form-input-icon
                    v-model="form.icon"
                    label="Icon"
                    placeholder="e.g fas fa-square-full"
                    required
                    :icon-classes="iconClasses"
                    :message="error('icon', null, errors)"
                />

                <biz-form-input
                    v-model="form.url"
                    label="Link"
                    placeholder="e.g https:://example.com/"
                    required
                    :message="error('url', null, errors)"
                />

                <biz-checkbox
                    v-model:checked="form.is_blank"
                    :value="true"
                >
                    Open link in a new tab
                </biz-checkbox>
            </fieldset>
        </form>
        <template #footer>
            <div
                class="columns"
                style="width: 100%"
            >
                <div class="column">
                    <div class="is-pulled-right">
                        <biz-button @click.prevent="onClose()">
                            Cancel
                        </biz-button>
                        <biz-button
                            class="is-primary ml-1"
                            type="button"
                            @click.prevent="onSubmit()"
                        >
                            {{ isCreate ? 'Create' : 'Update' }}
                        </biz-button>
                    </div>
                </div>
            </div>
        </template>
    </biz-modal-card>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizButton from '@/Biz/Button';
    import BizCheckbox from '@/Biz/Checkbox';
    import BizFormInput from '@/Biz/Form/Input';
    import BizFormInputIcon from '@/Biz/Form/InputIcon';
    import BizModalCard from '@/Biz/ModalCard';
    import fontawesomeBrandClasses from '@/Json/fontawesome-brand-classes';
    import { isBlank } from '@/Libs/utils';
    import { cloneDeep } from 'lodash';
    import { usePage } from '@inertiajs/inertia-vue3';
    import { reactive } from 'vue';

    export default {
        name: 'FooterSocialMediaForm',

        components: {
            BizButton,
            BizCheckbox,
            BizFormInput,
            BizFormInputIcon,
            BizModalCard,
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
