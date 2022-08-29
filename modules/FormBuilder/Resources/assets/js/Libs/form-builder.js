import { generateElementId } from '@/Libs/utils';

export function onFormEditorClicked(event, contentConfigId) {
    if (! (
        event.target.closest('.component-configurable')
        || event.target.closest('.form-builder-content-config')
    )) {
        contentConfigId.value = '';
    }
}

export function createColumn() {
    return {
        id: generateElementId(),
        components: [],
    };
}