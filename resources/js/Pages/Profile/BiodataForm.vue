<template>
    <form-section v-show="isShown">
        <template #title>
            Profile
        </template>

        <template #form>
            <form-builder
                route-name="admin.profile.show"
                :entity-id="user.id"
                :locale="$page.props.userOriginLanguage"
                @loaded-empty-field="isShown = false"
                @loaded-forbidden="isShown = false"
                @loaded-successfully="isShown = true"
            >
                <template #buttons>
                    <div class="field">
                        <button class="button is-medium is-primary">
                            <span class="has-text-weight-bold">Update details</span>
                        </button>
                    </div>
                </template>
            </form-builder>
        </template>
    </form-section>
</template>

<script>
    import FormBuilder from '@/Form/Builder.vue';
    import FormSection from '@/Frontend/FormSection.vue';

    export default {
        name: 'BiodataForm',

        components: {
            FormBuilder,
            FormSection,
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
