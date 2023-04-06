<template>
    <div>
        <h5 class="title is-5 mb-2">
            {{ i18n.details }}
        </h5>

        <biz-form-input
            v-model="form.name"
            :label="i18n.name"
            required
            :message="error('name')"
        />

        <biz-form-input
            v-model="form.short_description"
            :label="i18n.short_description"
            required
            :message="error('short_description')"
        />

        <biz-form-textarea
            v-model="form.description"
            :label="i18n.description"
            required
            rows="3"
            :message="error('description')"
        />

        <biz-form-select
            v-model="form.status"
            :label="i18n.status"
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
            :text="i18n.check_in_required"
            :value="form.is_check_in_required"
        />

        <h5 class="title is-5 mt-5 mb-3">
            {{ i18n.visibility }}
        </h5>

        <biz-form-select
            v-model="form.roles"
            :label="i18n.roles"
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
            {{ i18n.gallery }}
        </h5>

        <biz-form-multiple-media-library
            v-model="form.gallery"
            :label="i18n.upload"
            :is-download-enabled="true"
            :is-upload-enabled="true"
            :mediums="gallery"
            :allow-multiple="true"
            :max-files="rules.maxProductFileNumber"
            :instructions="instructions.mediaLibrary"
            :message="error('gallery')"
        />
    </div>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizFormCheckboxToggle from '@/Biz/Form/CheckboxToggle.vue';
    import BizFormInput from '@/Biz/Form/Input.vue';
    import BizFormMultipleMediaLibrary from '@/Biz/Form/MultipleMediaLibrary.vue';
    import BizFormSelect from '@/Biz/Form/Select.vue';
    import BizFormTextarea from '@/Biz/Form/Textarea.vue';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        components: {
            BizFormCheckboxToggle,
            BizFormInput,
            BizFormMultipleMediaLibrary,
            BizFormSelect,
            BizFormTextarea,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        inject: {
            i18n: { default: () => ({
                details : 'Details',
                name : 'Name',
                short_description : 'Short Description',
                description : 'Description',
                status : 'Status',
                check_in_required : 'Is a check-in required?',
                visibility : 'Visibility',
                roles : 'Roles',
                select : 'Select',
                Gallery : 'Gallery',
                upload : 'Upload',
            }) },
        },

        props: {
            modelValue: { type: Object, required: true },
            statusOptions: { type: Array, required: true },
            roleOptions: { type: Array, required: true },
            gallery: { type: Array, default: () => [] },
            rules: { type: Object, required: true },
            imageMimes: { type: Array, required: true },
            instructions: {type: Object, default: () => {}},
        },

        setup(props, { emit }) {
            return {
                form: useModelWrapper(props, emit),
            };
        },
    };
</script>
