import * as bulmaToast from 'bulma-toast';

bulmaToast.setDefaults({
    closeOnClick: true,
    dismissible: true,
    duration: 1800,
    offsetTop: '3rem',
    pauseOnHover: true,
});

const toast = {
    success: (message) => {
        bulmaToast.toast({
            message: message,
            type: 'is-success',
        });
    },
    info: (message) => {
        bulmaToast.toast({
            message: message,
            type: 'is-info',
        });
    },
};

export default toast;
