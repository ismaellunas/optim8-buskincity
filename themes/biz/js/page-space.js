import './bootstrap';

import { createApp, defineAsyncComponent, ref } from 'vue';

const SELECTED_CARD_CLASS = 'is-selected-pitch';

const appPageSpace = createApp({
    components: {
        SpaceEvents: defineAsyncComponent(() =>
            import('./../../../modules/Space/Resources/assets/js/Frontend/SpaceEvents.vue')
        ),
    },

    setup() {
        return {
            selectedPitchId: ref(null),
            selectedPitchName: ref(null),
        };
    },

    mounted() {
        const root = document.getElementById('app-page-space');

        if (! root) {
            return;
        }

        const eventsTarget = document.getElementById('city-pitch-events');

        if (! eventsTarget) {
            return;
        }

        const cards = root.querySelectorAll('.city-pitch-card');

        if (! cards.length) {
            return;
        }

        cards.forEach((card) => {
            card.style.cursor = 'pointer';

            card.addEventListener('click', (event) => {
                if (event.target.closest('a, button:not(.select-pitch-btn)')) {
                    return;
                }

                const pitchId = parseInt(card.dataset.pitchId, 10);

                if (! pitchId) {
                    return;
                }

                this.selectedPitchId = pitchId;
                this.selectedPitchName = card.dataset.pitchName || null;

                cards.forEach((other) => {
                    other.classList.toggle(
                        SELECTED_CARD_CLASS,
                        parseInt(other.dataset.pitchId, 10) === pitchId
                    );
                });

                this.$nextTick(() => {
                    document
                        .getElementById('city-pitch-events')
                        ?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            });
        });
    },
});

appPageSpace.mount('#app-page-space');
