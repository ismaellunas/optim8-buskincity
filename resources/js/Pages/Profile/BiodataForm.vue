<template>
    <jet-form-section>
        <template #title>
            Biodata
        </template>

        <template #description>
            &nbsp;
        </template>

        <template #form>
            <form-biodata
                :name="formName"
            />
        </template>
    </jet-form-section>
</template>

<script>
    import JetFormSection from '@/Jetstream/FormSection'
    import FormBiodata from '@/Form/FormBuilder';

    export default {
        name: 'BiodataForm',

        components: {
            FormBiodata,
            JetFormSection,
        },

        data() {
            return {
                formName: 'biodata',
            };
        },
    }
</script>
