<template>
    <div class="columns">
        <div class="column">
            <div class="is-pulled-left">
                <b>Header Layout</b><br>
                Last Saved: {{ lastSaved }}
            </div>
        </div>
        <div class="column">
            <div class="is-pulled-right">
                <sdb-button
                    type="button"
                    class="is-primary ml-2"
                    @click="saveLayout()"
                >
                    <span>Save</span>
                </sdb-button>
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
</template>

<script>
    import SdbButton from '@/Sdb/Button';
    import { success as successAlert  } from '@/Libs/alert';
    import { usePage } from '@inertiajs/inertia-vue3';

    export default {
        name: 'HeaderLayout',

        components: {
            SdbButton,
        },

        props: {
            lastSaved: {
                type: String,
                default: '-',
            },
            setting: {
                type: Object,
                default: true
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