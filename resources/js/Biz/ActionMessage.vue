<template>
    <div>
        <transition
            leave-active-class="transition ease-in duration-1000"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isActive"
                class="text-sm text-gray-600"
            >
                <slot />
            </div>
        </transition>
    </div>
</template>

<script>
    export default {
        name: 'BizActionMessage',
        props: {
            isActive: {
                type: Boolean,
                default: false
            }
        },
    }
</script>
