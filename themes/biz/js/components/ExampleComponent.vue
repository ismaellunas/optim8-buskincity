<template>
    <div class="container">
        <div class="card">
            <header class="card-header">
                <p class="card-header-title is-centered">
                    Theme Vue Component
                </p>
                <button class="card-header-icon" aria-label="more options">
                    <span class="icon">
                        <i
                            aria-hidden="true"
                            class="fas fa-angle-down"
                        />
                    </span>
                </button>
            </header>
            <div class="card-content">
                <p class="title">
                    “Two things are infinite: the universe and human stupidity; and I'm not sure about the universe.”
                </p>
                <p class="subtitle">
                    - Albert Einsten ({{ time }}) -
                </p>
            </div>
            <footer class="card-footer">
                <p class="card-footer-item">
                    <biz-button
                        class="is-primary"
                        @click="toggleTimer"
                    >
                        View on &nbsp;Twitter
                    </biz-button>
                </p>
                <p class="card-footer-item">
                    <biz-button
                        class="is-warning"
                        @click="toggleTimer"
                    >
                        View on &nbsp;Facebook
                    </biz-button>
                </p>
            </footer>
        </div>
    </div>
</template>

<script>
    import BizButton from '@/Biz/Button';

    export default {
        components: {
            BizButton,
        },
        data() {
            return {
                time: 0,
                isRunning: false,
                interval: null
            };
        },
        mounted() {
            console.log('Component mounted.')
            this.toggleTimer();
        },
        methods: {
            toggleTimer() {
                if (this.isRunning) {
                    clearInterval(this.interval);
                    console.log('timer stops');
                } else {
                    this.interval = setInterval(this.incrementTime, 1000);
                }
                this.isRunning = !this.isRunning;
            },
            incrementTime() {
                this.time = parseInt(this.time) + 1;
            },
        },
    };
</script>
