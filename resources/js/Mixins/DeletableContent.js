import { confirmDelete } from '@/Libs/alert';

export default {
    props: [
        'id',
    ],
    emits: ['delete-content'],
    data() {
        return {
            emitDeleteContentName: 'delete-content',
        }
    },
    methods: {
        deleteContent() {
            const self = this;
            confirmDelete('Are you sure?').then((result) => {
                if (result.isConfirmed) {
                    self.$emit(self.emitDeleteContentName, self.id);

                    self.onContentDeleted();
                }
            })
        },
        onContentDeleted() {},
    }
}
