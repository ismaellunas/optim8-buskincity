import { find } from 'lodash';
import { usePage } from '@inertiajs/inertia-vue3';

export function isModuleActive(moduleName) {
    return find(usePage().props.value.modules, (module) => module == moduleName);
}

export function isEcommerceModuleActive() {
    return isModuleActive('Ecommerce');
}
