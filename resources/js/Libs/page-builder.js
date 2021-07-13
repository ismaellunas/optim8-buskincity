import { generateElementId } from './utils';

export function createColumn() {
    return {
        id: generateElementId(),
        components: [],
    };
}

export function createBlock() {
    return {
        id: generateElementId(),
        type: 'columns',
        columns: [],
    }
}
