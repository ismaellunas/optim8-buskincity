<template>
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <slot name="headerTitle" />
            </p>

            <button
                v-if="$slots.headerButton"
                class="card-header-icon"
                aria-label="more options"
            >
                <slot name="headerButton" />
            </button>
        </header>

        <div class="card-content">
            <slot />
        </div>

        <footer
            v-if="$slots.footer"
            class="card-footer"
        >
            <slot name="footer" />
        </footer>
    </div>
</template>

<script>
    export default {};
</script>
