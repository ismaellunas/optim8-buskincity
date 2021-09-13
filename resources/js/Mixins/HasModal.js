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
                this.onCloseModal();
            },
            onShownModal() {},
            onCloseModal() {},
    }
}
