<template>
    <jet-form-section v-show="isShown">
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
