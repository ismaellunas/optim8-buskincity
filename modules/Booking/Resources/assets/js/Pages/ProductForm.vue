<template>
    <div>
        <h5 class="title is-5 mb-2">
            Details
        </h5>

        <biz-form-input
            v-model="form.name"
            label="Name"
            required
            :message="error('name')"
        />

        <biz-form-input
            v-model="form.short_description"
            label="Short Description"
            required
            :message="error('short_description')"
        />

        <biz-form-textarea
            v-model="form.description"
            label="Description"
            required
            rows="3"
            :message="error('description')"
        />

        <biz-form-select
            v-model="form.status"
            label="Status"
            :message="error('status')"
        >
            <option
                v-for="statusOption in statusOptions"
                :key="statusOption.id"
                :value="statusOption.id"
            >
                {{ statusOption.value }}
            </option>
        </biz-form-select>

        <biz-form-checkbox-toggle
            v-model="form.is_check_in_required"
            text="Is a check-in required?"
            :value="form.is_check_in_required"
        />

        <h5 class="title is-5 mt-5 mb-3">
            Visibility
        </h5>

        <biz-form-select
            v-model="form.roles"
            label="Roles"
            :message="error('roles')"
        >
            <option
                v-for="(roleOption, index) in roleOptions"
                :key="index"
                :value="roleOption.id"
            >
                {{ roleOption.value }}
            </option>
        </biz-form-select>

        <h5 class="title is-5 mt-5 mb-3">
            Gallery
        </h5>

        <biz-form-file-upload
            ref="file_upload"
            v-model="form.gallery"
            label="Upload"
            :accepted-types="['image/jpeg', 'image/png']"
            :allow-multiple="true"
            :max-file-size="rules.maxProductFileSize"
            :max-files="rules.maxProductFileNumber"
            :media="gallery"
            :message="error('gallery')"
        >
            <template #medium="mediumProps">
                {{ mediumProps.file_url }}
            </template>
        </biz-form-file-upload>
    </div>
</template>

<script>
    import BizFormCheckboxToggle from '@/Biz/Form/CheckboxToggle.vue';
    import BizFormFileUpload from '@/Biz/Form/FileUpload.vue';
    import BizFormInput from '@/Biz/Form/Input.vue';
    import BizFormSelect from '@/Biz/Form/Select.vue';
    import BizFormTextarea from '@/Biz/Form/Textarea.vue';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        components: {
            BizFormCheckboxToggle,
            BizFormFileUpload,
            BizFormInput,
            BizFormSelect,
            BizFormTextarea,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        props: {
            modelValue: { type: Object, required: true },
            statusOptions: { type: Array, required: true },
            roleOptions: { type: Array, required: true },
            gallery: { type: Array, default: () => [] },
            rules: { type: Object, required: true },
            imageMimes: { type: Array, required: true },
        },

        setup(props, { emit }) {
            return {
                form: useModelWrapper(props, emit),
            };
        },
    };
</script>
