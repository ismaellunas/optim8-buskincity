export function getEmptyForm() {
    return {
        name: null,
        form_id: null,
        builders: {
            name: null,
            title: null,
            order: null,
            visibility: [],
            locations: [],
            fields: [],
        }
    };
}
