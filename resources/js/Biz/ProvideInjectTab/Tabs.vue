<template>
    <div>
        <div class="tabs" :class="class">
            <ul>
                <template
                    v-for="(tab, i) of tabs"
                    :key="i"
                >
                    <li
                        v-if="tab.props.isRendered"
                        :id="tab.props.tabId"
                        :class="active === i ? 'is-active' : ''"
                        @click="selectTab(i)"
                    >
                        <a><span>{{ tab.props.title }}</span></a>
                    </li>
                </template>
            </ul>
        </div>

        <div class="mt-3">
            <slot />
        </div>
    </div>
</template>

<script>
    import { provide, computed, ref } from "vue";

    export default {
        name: 'TabsProvideInjectTab',
        props: {
            modelValue: { type: [String, Number], default: "" },
            class: { type: [String, Object, Array], default: "" },
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
