<template>
    <biz-modal-card
        @close="$emit('close')"
    >
        <template #header>
            <p class="modal-card-title has-text-weight-bold">
                <slot name="header" />
            </p>

            <button
                aria-label="close"
                class="delete"
                @click="$emit('close')"
            />
        </template>

        <form @submit.prevent="submit">
            <biz-form-input
                v-model="form.name"
                label="Name"
                maxlength="32"
                :required="true"
                :message="error('name', null, formErrors)"
            />
        </form>

        <template #footer>
            <div
                class="columns"
                style="width: 100%"
            >
                <div class="column">
                    <div class="is-pulled-right">
                        <biz-button @click="$emit('close')">
                            Cancel
                        </biz-button>

                        <biz-button
                            class="is-primary ml-1"
                            type="button"
                            @click="submit"
                        >
                            {{ !form.id ? 'Create' : 'Update' }}
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
    import BizFormInput from '@/Biz/Form/Input';
    import BizModalCard from '@/Biz/ModalCard';
    import { isEmpty } from '@/Libs/utils';
    import { ref } from 'vue';
    import { cloneDeep } from 'lodash';

    export default {
        name: 'SpaceTypeFormModal',

        components: {
            BizButton,
            BizFormInput,
            BizModalCard,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        props: {
            selectedType: { type: Object, default: () => {} },
        },

        emits: [
            'close',
            'on-submit',
        ],

        setup(props) {
            let form = ref({
                name: null,
            });

            if (!isEmpty(props.selectedType)) {
                form = ref(cloneDeep(props.selectedType));
            }

            return {
                form,
            };
        },

        data() {
            return {
                formErrors: {},
            };
        },

        methods: {
            submit() {
                const self = this;

                axios.post(route('admin.api.spaces.settings.space-types.validate'), self.form)
                    .then((response) => {
                        self.$emit('on-submit', self.form);
                    })
                    .catch((error) => {
                        self.formErrors = error.response.data.errors;
                    });
            },
        },
    };
</script>
