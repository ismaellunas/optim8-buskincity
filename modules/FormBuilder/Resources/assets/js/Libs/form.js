export function getEmptyForm() {
    return {
        name: null,
        form_id: null,
        setting: {
            button: {
                text: null,
            },
        },
        field_groups: [
            getEmptyFieldGroup(),
        ]
    };
}

export function getEmptyFieldGroup() {
    return {
        title: null,
        order: null,
        fields: [],
    };
}
