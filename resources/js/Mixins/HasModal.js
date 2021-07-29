export default {
    data() {
        return {
            isModalOpen: false,
        };
    },
    methods: {
            openModal() {
                this.isModalOpen = true;
                this.afterModalOpen();
            },
            closeModal() {
                this.isModalOpen = false;
            },
            afterModalOpen() {},
    }
}
