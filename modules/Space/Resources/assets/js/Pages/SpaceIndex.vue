<template>
    <app-layout :title="title">
        <template #header>
            {{ title }}
        </template>

        <div class="box">
            <div class="columns">
                <div class="column">
                    <div class="is-pulled-right" />
                </div>
            </div>

            <nested-draggable
                class="px-2 py-4"
                :spaces="spaces"
                @on-end="onEnd"
            />
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import NestedDraggable from './NestedDraggable';
    import { useForm } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            AppLayout,
            NestedDraggable,
        },

        props: {
            spaces: { type: Array, required: true },
            title: { type: String, default: "" },
        },

        setup(props) {
            return {
                form: useForm({
                    spaces: props.spaces,
                }),
            };
        },

        methods: {
            onEnd(evt) {
                const toParent = evt.to.dataset.parent;
                const current = evt.item.dataset.id

                if (current) {
                    const url = route('admin.spaces.move-node', {
                        current: current,
                        parent: toParent
                    });

                    this.$inertia.post(url, {
                        replace: true,
                    });
                }
            },
        },
    };
</script>
