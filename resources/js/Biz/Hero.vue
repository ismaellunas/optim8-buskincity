<template>
    <section
        class="hero"
        :class="heroClasses"
    >
        <div class="hero-body">
            <slot />
        </div>
    </section>
</template>

<script>
    export default {
        name: 'BizHero',
        props: {
            size: {
                type: String,
                default: 'medium',
                validator(value) {
                    return ['small', 'medium', 'large', 'halfheight', 'fullheight'].includes(value);
                },
            },
        },
        computed: {
            heroClasses() {
                return {
                    'is-small': this.size === 'small',
                    'is-medium': this.size === 'medium',
                    'is-large': this.size === 'large',
                    'is-halfheight': this.size === 'halfheight',
                    'is-fullheight': this.size === 'fullheight',
                };
            },
        },
    };
</script>
