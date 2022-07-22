<template>
    <div>
        <biz-form-input
            v-model="space.name"
            label="Name"
            placeholder="e.g My Location"
            required
            maxlength="128"
            :message="error('name')"
        />
        <biz-form-select
            v-model="space.parent_id"
            class="is-fullwidth"
            label="Parent"
            :disabled="!!space.id"
            :message="error('parent_id')"
        >
            <option
                v-for="option in parentOptions"
                :key="option.id"
                :value="option.id"
            >
                {{ option.value }}
            </option>
        </biz-form-select>
        <biz-form-select
            v-model="space.type"
            class="is-fullwidth"
            label="Type"
            :message="error('type')"
        >
            <option
                v-for="option in typeOptions"
                :key="option.id"
                :value="option.id"
            >
                {{ option.value }}
            </option>
        </biz-form-select>
        <biz-form-input
            v-model="space.latitude"
            label="Latitude"
            :message="error('latitude')"
        />
        <biz-form-input
            v-model="space.longitude"
            label="Longitude"
            :message="error('longitude')"
        />
        <biz-form-textarea
            v-model="space.address"
            label="Address"
            placeholder="Address"
            rows="3"
            maxlength="500"
            :message="error('address')"
        />

        <slot />

        <slot name="action" />
    </div>
</template>

<script>
    import BizFormInput from '@/Biz/Form/Input';
    import BizFormSelect from '@/Biz/Form/Select';
    import BizFormTextarea from '@/Biz/Form/Textarea';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        components: {
            BizFormInput,
            BizFormSelect,
            BizFormTextarea,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        props: {
            modelValue: { type: Object, required: true },
            parentOptions: { type: Object, required: true },
            typeOptions: { type: Object, required: true },
        },

        setup(props, { emit }) {
            return {
                space: useModelWrapper(props, emit),
            };
        },
    };
</script>
