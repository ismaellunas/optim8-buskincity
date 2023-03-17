<template>
    <app-layout :title="title">
        <template #header>
            {{ title }}
        </template>

        <div class="box">
            <form @submit.prevent="submit">
                <div class="control">
                    <Biz-input
                        :disabled="false"
                        type="file"
                        @input="form.file = $event.target.files[0]"
                    />
                </div>
                <progress
                    v-if="form.progress"
                    :value="form.progress.percentage"
                    max="100"
                >
                    {{ form.progress.percentage }}%
                </progress>

                <div class="field is-grouped mt-4">
                    <div class="control">
                        <biz-button class="is-link">
                            Submit
                        </biz-button>
                    </div>
                    <div class="control">
                        <biz-button-link :href="route(baseRouteName+'.index')">
                            Cancel
                        </biz-button-link>
                    </div>
                </div>
            </form>
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout.vue';
    import BizButton from '@/Biz/Button.vue';
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import BizInput from '@/Biz/Input.vue';
    import { isBlank } from '@/Libs/utils';
    import { useForm } from '@inertiajs/vue3'

    export default {
        components: {
            AppLayout,
            BizButton,
            BizButtonLink,
            BizInput,
        },
        props: {
            record: Object,
            entityId: Number,
            errors: Object,
            baseRouteName: String,
            title: { type: String, required: true },
        },
        setup(props) {
            const isNew = isBlank(props.entityId);
            let form = null;
            let submitRoute = null;

            if (isNew) {
                form = useForm({
                    name: null,
                    file: null,
                })
                submitRoute = route('admin.media.store');
            } else {
                form = useForm({
                    name: null,
                    file: null,
                })
                submitRoute = route('admin.media.update', {id: props.entityId});
            }

            function submit() {
                form.post(submitRoute);
            };

            return {
                form,
                submit,
                isNew,
            };
        },
        data() {
            return {
                disableInput: false,
                isEditMode: true,
            };
        },
    }
</script>
