<template>
    <div v-if="isActive">
        <slot />
    </div>
</template>

<script>
    import { computed, inject, watchEffect, getCurrentInstance } from "vue";

    export default {
        name: 'TabProvideInjectTab',
        props: {
            title: { type: String, required: true },
            isRendered: {type: Boolean, default: true },
            tabId: { type: String, default: ''},
        },
        setup(props) {
            const instance = getCurrentInstance();
            const { tabs, active } = inject("tabsState");

            const index = computed(() =>
                tabs.value.findIndex((target) => target.uid === instance.uid)
            );
            const isActive = computed(() => index.value === active.value);

            watchEffect(() => {
                if (index.value === -1) {
                    tabs.value.push(instance);
                }
            });

            return {
                isActive,
            };
        },
    };
</script>
