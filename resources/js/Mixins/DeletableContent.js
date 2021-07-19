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
            if (confirm('Are you sure?')) {
                this.$emit(this.emitDeleteContentName, this.id);
            }
        }
    }
}
