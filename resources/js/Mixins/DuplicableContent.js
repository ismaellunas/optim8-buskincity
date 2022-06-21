import { confirm } from '@/Libs/alert';

export default {
    props: [
        'id',
    ],
    emits: ['duplicate-content'],
    data() {
        return {
            emitDuplicateContentName: 'duplicate-content',
        }
    },
    methods: {
        duplicateContent() {
            const self = this;

            confirm(
                'Duplicate Component?'
            ).then((result) => {
                if (result.isConfirmed) {
                    self.$emit(self.emitDuplicateContentName, self.id);

                    self.onContentDuplicated();
                }
            });
        },
        onContentDuplicated() {},
    }
}
