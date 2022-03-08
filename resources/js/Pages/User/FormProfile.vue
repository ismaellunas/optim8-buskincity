<template>
    <div class="mb-3">
        <biz-form-image-editable
            v-model="form.photo"
            v-model:photo-url="form.photo_url"
            delete-label="Remove Photo"
            modal-label="Profile Photo"
            :message="error('photo')"
            :photo-url="form.photo_url"
            :show-delete-button="form.photo_url != null"
            @on-reset-value="resetImageForm()"
            @on-delete-image="onDeleteImage()"
        />

        <biz-form-input
            v-model="form.first_name"
            label="First Name"
            required
            :message="error('first_name')"
        />

        <biz-form-input
            v-model="form.last_name"
            label="Last Name"
            required
            :message="error('last_name')"
        />

        <biz-form-input
            v-model="form.email"
            label="Email"
            required
            type="email"
            :message="error('email')"
        />

        <biz-form-select
            v-if="canSetRole"
            v-model="form.role"
            label="Role"
            placeholder="- Select a Role -"
            :message="error('role')"
        >
            <option
                v-for="option in roleOptions"
                :key="option.id"
                :value="option.id"
            >
                {{ option.value }}
            </option>
        </biz-form-select>
    </div>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizFormInput from '@/Biz/Form/Input';
    import BizFormImageEditable from '@/Biz/Form/ImageEditable';
    import BizFormSelect from '@/Biz/Form/Select';
    import { useModelWrapper } from '@/Libs/utils';
    import { confirmDelete } from '@/Libs/alert';

    export default {
        name: 'UserProfileForm',

        components: {
            BizFormInput,
            BizFormImageEditable,
            BizFormSelect,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        props: {
            canSetRole: {type: Boolean, default: true},
            modelValue: {},
            photoUrl: {type: [String, null], default: null},
            roleOptions: {type: Array, default: () => []},
        },

        setup(props, { emit }) {
            return {
                form: useModelWrapper(props, emit),
            };
        },

        methods: {
            resetImageForm() {
                this.form.reset('photo', 'photo_url', 'profile_photo_media_id');
            },

            onDeleteImage() {
                const self = this;

                confirmDelete().then((result) => {
                    if (result.isConfirmed) {
                        self.form.photo = null;
                        self.form.photo_url = null;
                        self.form.profile_photo_media_id = null;
                    }
                })
            },
        },
    };
</script>
