<template>
    <sdb-form-section v-show="isShown">
        <template #title>
            {{ title }}
        </template>

        <template #description>
            &nbsp;
        </template>

        <template #form>
            <form-biodata
                :name="formName"
                @loaded-forbidden="onLoadedForbidden"
                @loaded-successfully="onLoadedSuccessfully"
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
                isShown: false,
                title: '',
            };
        },

        methods: {
            onLoadedForbidden() {
                this.isShown = false;
            },

            onLoadedSuccessfully(data) {
                this.isShown = true;
                this.title = data.title;
            },
        }
    }
</script>
