export default {
    title: 'Carousel',
    componentName: 'Carousel',
    content: {
        carousel: {
            image: [
                {
                    mediaId: null,
                }
            ],
        },
        template: {
            mediaId: null,
        },
    },
    config: {
        carousel: {
            numberOfSliders: 1,
            autoPlay: "active",
        }
    }
}

export const config = {
    carousel: {
        label: "Carousel",
        config: {
            numberOfSliders: {
                type: "select",
                label: "Number of Sliders",
                options: [
                    { value: 1, name: "1"},
                    { value: 2, name: "2"},
                    { value: 3, name: "3"},
                    { value: 4, name: "4"},
                    { value: 5, name: "5"},
                    { value: 6, name: "6"},
                ],
            },
            autoPlay: {
                type: "select",
                label: "Auto Play",
                options: [
                    { value: "", name: "No"},
                    { value: "active", name: "Yes"},
                ],
            },
        }
    }
};
