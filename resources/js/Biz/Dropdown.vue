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
        >
            <biz-button
                v-if="isTriggerButton"
                :aria-controls="menuId"
                :style="styleButton"
                aria-haspopup="true"
                class="button"
                type="button"
                :class="classButton"
            >
                <slot name="trigger" />
            </biz-button>

            <a
                v-else
                aria-haspopup="true"
                :aria-controls="menuId"
            >
                <slot name="trigger" />
            </a>
        </div>

        <div
            v-show="isActive || isHoverable"
            :id="menuId"
            ref="dropdownMenu"
            class="dropdown-menu"
            role="menu"
        >
            <div class="dropdown-content">
                <slot />
            </div>
        </div>
    </div>
</template>

<script>
    import BizButton from '@/Biz/Button';
    import { ref } from "vue";

    export default {
        name: 'BizDropdown',

        components: {
            BizButton,
        },

        provide() {
            return {
                selectItem: this.selectItem,
            };
        },

        props: {
            active: {
                type: Boolean,
                default: false
            },
            closeOnClick: {
                type: Boolean,
                default: true
            },
            isHoverable: {
                type: Boolean,
                default: false
            },
            menuId: {
                type: String,
                default: 'dropdown-menu'
            },
            styleButton: {
                type: String,
                default: ""
            },
            classButton: {
                type: [Array, Object, String],
                default: ""
            },
            isTriggerButton: {
                type: Boolean,
                default: true
            },
            isFullwidth: {
                type: Boolean,
                default: false
            },
        },

        emits: [
            'on-click'
        ],

        setup(props) {
            const isActive = ref(props.active);

            return {
                isActive,
            };
        },

        computed: {
            rootClasses() {
                return {
                    'is-active': this.isActive,
                    'is-hoverable': this.isHoverable,
                    'is-fullwidth': this.isFullwidth,
                };
            },
        },

        created() {
            if (typeof window !== 'undefined') {
                document.addEventListener('click', this.clickedOutside);
                document.addEventListener('keyup', this.keyPress);
            }
        },

        beforeUnmount() {
            if (typeof window !== 'undefined') {
                document.removeEventListener('click', this.clickedOutside);
                document.removeEventListener('keyup', this.keyPress);
            }
        },

        methods: {
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
                this.$emit('on-click');
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
                            return true;
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
                            return true;
                        }
                    }
                }
                return false;
            },

            clickedOutside(event) {
                const target = event.target;
                if (!this.isInWhiteList(target)) {
                    this.isActive = false;
                }
            },

            keyPress({ key }) {
                if (this.isActive && (key === 'Escape' || key === 'Esc')) {
                    this.isActive = false;
                }
            },
        },
    };
</script>
