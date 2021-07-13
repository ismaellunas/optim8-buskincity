<template>
    <app-layout>
        <template #header>
            <h2 class="">Media</h2>
        </template>

        <div class="box">
            <form @submit.prevent="submit">
                <div class="control">
                    <sdb-input
                        :disabled="false"
                        type="file"
                        @input="form.file = $event.target.files[0]"
                        />
                </div>
                <progress v-if="form.progress" :value="form.progress.percentage" max="100">
                    {{ form.progress.percentage }}%
                </progress>

                <div class="field is-grouped mt-4">
                    <div class="control">
                        <button class="button is-link">Submit</button>
                    </div>
                    <div class="control">
                        <inertia-link :href="route(baseRouteName+'.index')" class="button">
                            Cancel
                        </inertia-link>
                    </div>
                </div>
            </form>
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import SdbButton from '@/Sdb/Button';
    import SdbInput from '@/Sdb/Input';
    import { Inertia } from "@inertiajs/inertia";
    import { isBlank } from '@/Libs/utils';
    import { reactive } from "vue";
    import { useForm } from '@inertiajs/inertia-vue3'

    export default {
        components: {
            AppLayout,
            SdbButton,
            SdbInput,
        },
        props: {
            record: Object,
            entityId: Number,
            errors: Object,
            baseRouteName: String,
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
