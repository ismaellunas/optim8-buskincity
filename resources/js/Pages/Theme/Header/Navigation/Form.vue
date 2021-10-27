<template>
    <sdb-modal-card>
        <template v-slot:header>
            <p class="modal-card-title has-text-weight-bold">Add New Menu</p>
            <button class="delete" aria-label="close" @click="$emit('close')"></button>
        </template>
        <form method="post">
            <fieldset>
                <sdb-form-input
                    v-model="form.title"
                    label="Title"
                    required
                    :message="form.errors.title"
                ></sdb-form-input>
            </fieldset>
        </form>
        <template v-slot:footer>
            <div class="columns" style="width: 100%">
                <div class="column">
                    <div class="is-pulled-right">
                        <sdb-button @click="$emit('close')">Cancel</sdb-button>
                        <sdb-button
                            @click="onSubmit()"
                            class="is-primary ml-1"
                        >Create</sdb-button>
                    </div>
                </div>
            </div>
        </template>
    </sdb-modal-card>
</template>

<script>
    import SdbButton from '@/Sdb/Button';
    import SdbFormInput from '@/Sdb/Form/Input';
    import SdbModalCard from '@/Sdb/ModalCard';
    import { useForm } from '@inertiajs/inertia-vue3';
    import { success as successAlert } from '@/Libs/alert';

    export default {
        name: 'ModalForm',

        components: {
            SdbButton,
            SdbFormInput,
            SdbModalCard,
        },

        props: {
            baseRouteName: String,
        },

        setup() {
            const form = {
                title: null,
                is_active: true,
            };

            return {
                form: useForm(form),
            };
        },

        data() {
            return {
                loader: null,
            };
        },

        methods: {
            onSubmit() {
                const self = this;
                const form = this.form;
                form.post(route(this.baseRouteName+'.store'), {
                    preserveScroll: false,
                    onStart: () => {
                        self.loader = self.$loading.show();
                        self.isProcessing = true;
                    },
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                        form.isDirty = false;
                        form.reset();
                    },
                    onFinish: () => {
                        self.loader.hide();
                        self.isProcessing = false;
                        this.$emit('close');
                    }
                });
            },
        },
    }
</script>