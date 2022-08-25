export function getEmptyForm() {
    return {
        name: null,
        key: null,
        builders: {
            name: null,
            title: null,
            order: null,
            visibility: null,
            locations: null,
            fields: [],
        }
    };
}
