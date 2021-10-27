<template>
    <app-layout>
        <template #header>
            Main Menu
        </template>

        <sdb-error-notifications :errors="$page.props.errors"/>

        <form method="post">
            <div class="box mb-6">
                <div class="columns">
                    <div class="column">
                        <fieldset>
                            <sdb-form-input
                                v-model="form.title"
                                label="Title"
                                required
                                :message="form.errors.title"
                            ></sdb-form-input>
                        </fieldset>
                    </div>
                </div>
            </div>
            <div class="box mb-6">
                <div class="columns">
                    <div class="column">
                        <div class="is-pulled-left">
                            <b>Menu Items</b>
                        </div>
                    </div>
                    <div class="column">
                        <div class="is-pulled-right">
                            <sdb-button
                                class="is-primary"
                                @click="isModalOpen = true"
                            >
                                <span class="icon is-small">
                                    <i class="fas fa-plus"></i>
                                </span>
                                <span>Add Menu Item</span>
                            </sdb-button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import MixinHasModal from '@/Mixins/HasModal';
    import SdbButton from '@/Sdb/Button';
    import SdbButtonLink from '@/Sdb/ButtonLink';
    import SdbErrorNotifications from '@/Sdb/ErrorNotifications';;
    import SdbFormInput from '@/Sdb/Form/Input';
    import { useForm } from '@inertiajs/inertia-vue3';
    import { confirmDelete } from '@/Libs/alert';
    import { merge } from 'lodash';

    export default {
        components: {
            AppLayout,
            SdbButton,
            SdbButtonLink,
            SdbErrorNotifications,
            SdbFormInput,
        },
        mixins: [
            MixinHasModal,
        ],
        props: {
            baseRouteName: String,
            menu: Object,
        },
        setup(props) {
            const form = merge(
                props.menu,
                {
                    menu_items: {
                        en: [],
                    }
                }
            );
            return {
                form: useForm(form),
            }
        },
        data() {
            return {
                //
            };
        },
        methods: {
            deleteRow(record) {
                const self = this;
                confirmDelete().then((result) => {
                    if (result.isConfirmed) {
                        self.$inertia.delete(
                            route(self.baseRouteName+'.destroy', record.id)
                        );
                    }
                });
            },
        }
    }
</script>
