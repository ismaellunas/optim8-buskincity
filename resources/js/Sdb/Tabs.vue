<template>
    <div class="tabs" :class="class">
        <ul>
            <li
                v-for="(tab, i) of tabs"
                :key="i"
                :class="active === i ? 'is-active' : ''"
                @click="selectTab(i)"
            >
                <a><span>{{ tab.props.title }}</span></a>
            </li>
        </ul>
    </div>

    <div class="mt-3">
        <slot />
    </div>
</template>

<script>
    import { provide, computed, ref } from "vue";

    export default {
        props: {
            modelValue: {
                type: [String, Number],
                class: {},
            },
            class: {},
        },
        emits: ["update:modelValue"],
        setup(props, { slots, emit }) {
            const active = computed(() => props.modelValue);
            const tabs = ref([]);

            function selectTab(tab) {
                emit("update:modelValue", tab);
            }

            provide("tabsState", {
                active,
                tabs,
            });

            return {
                tabs,
                active,
                selectTab,
            };
        },
    }
</script>
