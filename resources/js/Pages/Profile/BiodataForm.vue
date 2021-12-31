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

        <template #actions>
            <!--
            <jet-action-message :on="form.recentlySuccessful" class="mr-3">
                Saved.
            </jet-action-message>

            <jet-button :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Save
            </jet-button>
            -->
        </template>
    </jet-form-section>
</template>

<script>
    import JetActionMessage from '@/Jetstream/ActionMessage'
    import JetButton from '@/Jetstream/Button'
    import JetFormSection from '@/Jetstream/FormSection'
    import JetInput from '@/Jetstream/Input'
    import JetInputError from '@/Jetstream/InputError'
    import JetLabel from '@/Jetstream/Label'
    import FormBiodata from '@/Form/FormBuilder';

    export default {
        name: 'BiodataForm',

        components: {
            FormBiodata,
            //JetActionMessage,
            //JetButton,
            JetFormSection,
            //JetInput,
            //JetInputError,
            //JetLabel,
        },

        data() {
            return {
                formName: 'biodata',
            };
        },

        methods: {},
    }
</script>
