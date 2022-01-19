<template>
    <biz-form-section v-show="isShown">
        <template #title>
            Profile
        </template>

        <template #description>
            &nbsp;
        </template>

        <template #form>
            <form-builder
                route-name="admin.profile.show"
                :entity-id="user.id"
                @loaded-forbidden="isShown = false"
                @loaded-successfully="isShown = true"
            />
        </template>
    </biz-form-section>
</template>

<script>
    import FormBuilder from '@/Form/Builder';
    import BizFormSection from '@/Biz/FormSection';

    export default {
        name: 'BiodataForm',

        components: {
            FormBuilder,
            BizFormSection,
        },

        props: {
            user: {type: Object, required: true}
        },

        data() {
            return {
                isShown: false,
            };
        },
    };
</script>
