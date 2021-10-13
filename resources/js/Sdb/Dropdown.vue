<template>
    <div
        ref="dropdown"
        class="dropdown"
        :class="rootClasses"
    >
        <div
            ref="trigger"
            class="dropdown-trigger"
            @click="toggle"
            @mouseenter="onHover"
            @mouseout="onMouseOut"
        >
            <sdb-button
                :aria-controls="menuId"
                aria-haspopup="true"
                class="button"
                type="button"
            >
                <slot name="trigger"></slot>
            </sdb-button>
        </div>

        <div
            v-show="isActive || isHoverable"
            :id="menuId"
            ref="dropdownMenu"
            class="dropdown-menu"
            role="menu"
            @mouseout.self="onMouseOut"
        >
            <div class="dropdown-content">
                <slot></slot>
            </div>
        </div>
    </div>
</template>

<script>
    import SdbButton from '@/Sdb/Button';
    import { onMounted, onUnmounted, ref } from "vue";
    import { isBlank, isEmpty } from "@/Libs/utils";
    import { uniqueId } from "lodash";

    export default {
        name: 'SdbDropdown',

        components: {
            SdbButton,
        },

        props: {
            active: {
                type: Boolean,
                default: false
            },
            menuId: {
                type: String,
                default: 'dropdown-menu'
            },
            closeOnClick: {
                type: Boolean,
                default: true
            },
            isHoverable: {
                type: Boolean,
                default: false
            }
        },

        setup(props) {
            const isActive = ref(props.active);

            return {
                isActive,
            };
        },

        provide() {
            return {
                selectItem: this.selectItem,
            };
        },

        methods: {
            onMouseOut(event) {
                if (this.isHoverable && !this.isInWhiteList(event)) {
                    this.isActive = false;
                }
            },

            onHover() {
                if (this.isHoverable) {
                    this.isActive = true;
                }
            },

            selectItem() {
                    this.isActive = !this.closeOnClick;
            },

            toggle() {
                this.isActive = !this.isActive;
            },

            isInWhiteList(el) {
                if (el === this.$refs.dropdownMenu) return true
                if (el === this.$refs.trigger) return true

                // All chidren from dropdown
                if (
                    this.$refs.dropdownMenu !== undefined
                    && this.$refs.dropdownMenu !== null
                ) {
                    const children = this.$refs.dropdownMenu.querySelectorAll('*')
                    for (const child of children) {
                        if (el === child) {
                            return true
                        }
                    }
                }
                // All children from trigger
                if (
                    this.$refs.trigger !== undefined
                    && this.$refs.trigger !== null
                ) {
                    const children = this.$refs.trigger.querySelectorAll('*')
                    for (const child of children) {
                        if (el === child) {
                            return true
                        }
                    }
                }
                return false
            },

            clickedOutside(event) {
                const target = event.target;
                if (!this.isInWhiteList(target)) {
                    this.isActive = false
                }
            },

            keyPress({ key }) {
                if (this.isActive && (key === 'Escape' || key === 'Esc')) {
                    this.isActive = false
                }
            },
        },

        computed: {
            rootClasses() {
                return {
                    'is-active': this.isActive,
                    'is-hoverable': this.isHoverable,
                };
            },
        },

        created() {
            if (typeof window !== 'undefined') {
                document.addEventListener('click', this.clickedOutside);
                document.addEventListener('keyup', this.keyPress);
            }
        },

        beforeDestroy() {
            if (typeof window !== 'undefined') {
                document.removeEventListener('click', this.clickedOutside);
                document.removeEventListener('keyup', this.keyPress);
            }
        }
    };
</script>
