import { generateElementId } from '@/Libs/utils';

export function onFormEditorClicked(event, inputConfigId) {
    if (! (
        event.target.closest('.component-configurable')
        || event.target.closest('.form-builder-input-config')
    )) {
        inputConfigId.value = '';
    }
}