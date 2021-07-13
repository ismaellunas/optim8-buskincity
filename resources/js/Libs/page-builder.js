import { generateElementId } from './utils';

export function createColumn() {
    return {
        id: generateElementId(),
        components: [],
    };
}
