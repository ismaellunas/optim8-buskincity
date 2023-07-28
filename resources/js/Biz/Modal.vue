<template>
    <div
        class="modal"
        :class="{'is-active': isActive}"
    >
        <div class="modal-background" />
        <div
            class="modal-content"
            :class="contentClass"
        >
            <slot />
        </div>
        <button
            v-show="!isCloseHidden"
            class="modal-close is-large"
            type="button"
            @click="$emit('close')"
        />
    </div>
</template>

<script>
    import { onMounted, onUnmounted } from 'vue';
    import { isEmpty } from 'lodash';

    export default {
        name: 'BizModal',

        props: {
            contentClass: { type: [Array, Object, String], default: null },
            isActive: { type: Boolean, default: true },
            isCloseHidden: { type: Boolean, default: false },
        },

        emits: ['close'],

        setup(props, { emit }) {
            const clickListener = function (event) {
                const path = event.path || (event.composedPath && event.composedPath());
                if (path) {
                    const isFound = path.find((elm) => {
                        if (!isEmpty(elm.classList)) {
                            return (
                                elm.classList.value.includes('modal-background')
                                || elm.classList.value.includes('modal-close')
                                || (
                                    elm.classList.value.includes('modal-card-head')
                                    && elm.classList.value.includes('delete')
                                )
                                || (
                                    elm.classList.value.includes('modal-card-foot')
                                    && elm.classList.value.includes('button')
                                )
                            );
                        }
                        return false;
                    })

                    if (isFound !== undefined) {
                        emit('close');
                    }
                }
            }

            onMounted(() => {
                window.addEventListener("click", clickListener);
            });

            onUnmounted(() => {
                window.removeEventListener("click", clickListener);
            });
        }
    };
</script>
