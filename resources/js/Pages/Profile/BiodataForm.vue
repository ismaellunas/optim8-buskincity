<template>
    <sdb-form-section>
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
    </sdb-form-section>
</template>

<script>
    import FormBiodata from '@/Form/FormBuilder';
    import SdbFormSection from '@/Sdb/FormSection';

    export default {
        name: 'BiodataForm',

        components: {
            FormBiodata,
            SdbFormSection,
        },

        data() {
            return {
                formName: 'biodata',
            };
        },
    }
</script>
