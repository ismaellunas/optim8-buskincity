import Swal from 'sweetalert2';
import { assign, clone, get, has } from 'lodash';

const timer = 1800;

const defaultConfig = {
    scrollbarPadding: false,
    customClass: {
        container: 'swal2-container-custom',
    },
};

const nonSweetAlertOptions = {
    isScrollToTop: true
};

function scrollToTop() {
    window.scrollTo(0,0);
};

function takeNonSweetAlertOptions(options)
{
    const takenOptions = clone(nonSweetAlertOptions);

    Object.keys(nonSweetAlertOptions).forEach((key) => {
        if (has(options, key)) {
            takenOptions[key] = get(options, key);

            delete options[key];
        }
    })

    return takenOptions;
}

export function success(title, message) {
    Swal.fire(assign(
        clone(defaultConfig),
        {
            icon: 'success',
            title: title,
            text: message,
            showConfirmButton: false,
            timer: timer,
            didClose: () => window.scrollTo(0,0),
        }
    ));
};

export function failed(title, message) {
    Swal.fire(assign(
        clone(defaultConfig),
        {
            icon: 'error',
            title: title,
            text: message,
            showConfirmButton: false,
            timer: 3000,
            didClose: () => window.scrollTo(0,0),
        }
    ));
};

export function confirm(title, message, confirmButtonText = "Yes", additionalConfig = {}) {
    return Swal.fire(assign(
        clone(defaultConfig),
        {
            title: title,
            text: message,
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: confirmButtonText,
        },
        additionalConfig
    ));
};

export function confirmDelete(
    title = 'Are you sure?',
    message,
    confirmButtonText = "Yes"
) {
    return confirm(title, message, confirmButtonText, {
        icon: 'warning',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
    });
};

export function oops(options) {
    options = assign({
        title: "Oops...",
        text: "Something went wrong!",
    }, options);

    const takenOptions = takeNonSweetAlertOptions(options);

    return Swal.fire(assign(
        clone(defaultConfig),
        {
            icon: 'error',
            didClose: () => {
                if (takenOptions.isScrollToTop) {
                    scrollToTop();
                }
            },
        },
        options
    ));
};

export function confirmLeaveProgress(
    title = 'Are you sure?',
    message = 'It looks like you have been editing something. If you leave before saving, your changes will be lost.',
    confirmButtonText = 'Leave this',
    cancelButtonText = 'Continue Editing'
) {
    return Swal.fire(assign(
        clone(defaultConfig),
        {
            title: title,
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: confirmButtonText,
            cancelButtonText: cancelButtonText,
        }
    ));
}
