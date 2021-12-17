import { defaultOption, imageRatios } from './style-options';

export default {
    title: 'Carousel',
    componentName: 'Carousel',
    content: {
        carousel: {
            images: [
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
            autoPlay: true,
            // figure
            ratio: "is-16by9",
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
                    { value: false, name: "No"},
                    { value: true, name: "Yes"},
                ],
            },
            ratio: {
                type: "select",
                label: "Ratio",
                options: defaultOption.concat(imageRatios)
            },
        }
    }
};
