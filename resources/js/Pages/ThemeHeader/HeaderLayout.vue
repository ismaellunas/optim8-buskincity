<template>
    <div>
        <div class="columns">
            <div class="column">
                <div class="is-pulled-left">
                    <b>Header Layout</b><br>
                    Last Saved: {{ setting.updated_at }}
                </div>
            </div>
        </div>
        <div class="columns">
            <div class="column is-3">
                <div
                    class="card card-hover"
                    :class="form.layout == 1 ? 'is-active' : ''"
                    @click="form.layout = 1"
                >
                    <div class="card-content">
                        <div class="content">
                            Layout 1
                        </div>
                    </div>
                </div>
            </div>
            <div class="column is-3">
                <div
                    class="card card-hover"
                    :class="form.layout == 2 ? 'is-active' : ''"
                    @click="form.layout = 2"
                >
                    <div class="card-content">
                        <div class="content">
                            Layout 2
                        </div>
                    </div>
                </div>
            </div>
            <div class="column is-3">
                <div
                    class="card card-hover"
                    :class="form.layout == 3 ? 'is-active' : ''"
                    @click="form.layout = 3"
                >
                    <div class="card-content">
                        <div class="content">
                            Layout 3
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import { success as successAlert  } from '@/Libs/alert';
    import { usePage } from '@inertiajs/inertia-vue3';

    export default {
        name: 'HeaderLayout',

        props: {
            setting: {
                type: Object,
                required: true
            },
        },

        setup() {
            return {
                baseRouteName: usePage().props.value.baseRouteName ?? null,
            };
        },

        data() {
            return {
                form: {
                    layout: parseInt(this.setting.value),
                },
            };
        },

        methods: {
            saveLayout() {
                this.$inertia.post(
                    route(this.baseRouteName+".layout.update"),
                    this.form,
                    {
                        onSuccess: (page) => {
                            successAlert(page.props.flash.message);
                        }
                    }
                )
            },
        },
    }
</script>

<style scoped>
    .is-active {
        border: 2px solid rgb(67, 67, 235);
    }

    .card-hover {
        cursor: pointer;
    }
</style>