<template>
    <biz-modal-card @close="$emit('close')">
        <template #header>
            <p class="modal-card-title has-text-weight-bold">
                {{ isCreate ? i18n.add_social_media : i18n.edit_social_media }}
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
                    :label="i18n.icon"
                    placeholder="e.g fas fa-square-full"
                    required
                    :icon-classes="iconClasses"
                    :message="error('icon', null, errors)"
                />

                <biz-form-input
                    v-model="form.url"
                    :label="i18n.link"
                    placeholder="e.g https:://example.com/"
                    required
                    :message="error('url', null, errors)"
                />

                <biz-checkbox
                    v-model:checked="form.is_blank"
                    :value="true"
                >
                    <span class="ml-2">
                        {{ i18n.open_link }}
                    </span>
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
                            {{ i18n.cancel }}
                        </biz-button>
                        <biz-button
                            class="is-primary ml-1"
                            type="button"
                            @click.prevent="onSubmit()"
                        >
                            {{ isCreate ? i18n.create : i18n.update }}
                        </biz-button>
                    </div>
                </div>
            </div>
        </template>
    </biz-modal-card>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizButton from '@/Biz/Button.vue';
    import BizCheckbox from '@/Biz/Checkbox.vue';
    import BizFormInput from '@/Biz/Form/Input.vue';
    import BizFormInputIcon from '@/Biz/Form/InputIcon.vue';
    import BizModalCard from '@/Biz/ModalCard.vue';
    import fontawesomeBrandClasses from '@/Json/fontawesome-brand-classes.json';
    import { isBlank } from '@/Libs/utils';
    import { cloneDeep } from 'lodash';
    import { usePage } from '@inertiajs/vue3';
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

        inject: {
            i18n: { default: () => ({
                add_social_media : 'Add social media',
                edit_social_media : 'Edit social media',
                icon : 'Icon',
                link : 'Link',
                cancel : 'Cancel',
                create : 'Create',
                update : 'Update',
                open_link : 'Open link in a new tab',
            }) },
        },

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
                baseRouteName: usePage().props.baseRouteName ?? null,
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
