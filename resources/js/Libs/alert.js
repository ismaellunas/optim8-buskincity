import Swal from 'sweetalert2';
import { assign, clone } from 'lodash';

const timer = 1800;
const defaultConfig = {
    scrollbarPadding: false,
};

export function success(title, message) {
    Swal.fire(assign(
        clone(defaultConfig),
        {
            icon: 'success',
            title: title,
            text: message,
            showConfirmButton: false,
            timer: timer,
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

export function oops(title = "Oops...", message = "Something went wrong!") {
    return Swal.fire({
        icon: 'error',
        title: title,
        text: message,
    })
};
