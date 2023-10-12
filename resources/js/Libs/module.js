import { find } from 'lodash';
import { usePage } from '@inertiajs/vue3';

export function isModuleActive(moduleName) {
    return !!find(usePage().props.modules, (module) => module == moduleName);
}

export function isEcommerceModuleActive() {
    return isModuleActive('Ecommerce');
}

function confirmationHtml(messages, title) {
    const content = document.createElement('div');
    content.className = 'content has-text-left';

    if (messages && messages.length > 0) {
        const ol = document.createElement('ol');

        messages.forEach((message) => {
            const li = document.createElement('li');

            li.innerHTML = message;

            ol.appendChild(li);
        });

        content.appendChild(ol);
    }

    if (title) {
        const heading = document.createElement('h3');
        heading.className = 'title is-3 has-text-centered';
        heading.innerHTML = title;

        content.appendChild(heading);
    }

    return content.outerHTML;
}

export async function activationAlertConfigs(url)
{
    const configs = {};
    const confirmation = await axios.get(url);

    configs.icon = 'info';
    configs.html = confirmationHtml(
        confirmation.data.messages,
        confirmation.data.title
    );

    return configs;
}
