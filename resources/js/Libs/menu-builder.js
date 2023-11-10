export function moveItemUp(index, items) {
    if (index > 0 && index < items.length) {
        const item = items[index];

        items.splice(index, 1);
        items.splice(index - 1, 0, item);
    }
}

export function moveItemDown(index, items) {
    if (index >= 0 && index < items.length - 1) {
        const item = items[index];

        items.splice(index, 1);
        items.splice(index + 1, 0, item);
    }
}
