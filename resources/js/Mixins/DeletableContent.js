export default {
    props: [
        'id',
    ],
    data() {
        return {
            emitDeleteContentName: 'delete-content',
        }
    },
    methods: {
        deleteContent() {
            this.$emit(this.emitDeleteContentName, this.id);
        }
    }
}
