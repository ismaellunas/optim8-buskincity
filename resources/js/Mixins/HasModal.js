export default {
    data() {
        return {
            isModalOpen: false,
        };
    },
    methods: {
            openModal() {
                this.isModalOpen = true;
                this.onShownModal();
            },
            closeModal() {
                this.isModalOpen = false;
            },
            onShownModal() {},
    }
}
