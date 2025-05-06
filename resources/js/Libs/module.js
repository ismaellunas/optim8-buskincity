import { find } from 'lodash';
import { usePage } from '@inertiajs/vue3';

export function isModuleActive(moduleName) {
    return find(usePage().props.modules, (module) => module == moduleName);
}

export function isEcommerceModuleActive() {
    return isModuleActive('Ecommerce');
}
