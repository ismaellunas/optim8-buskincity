import Swal from 'sweetalert2';
import { assign } from 'lodash';

const defaultConfig = {
    scrollbarPadding: false,
};

export function success(message = "Your work has been saved") {
    Swal.fire(assign(
        defaultConfig,
        {
            icon: 'success',
            title: message,
            showConfirmButton: false,
            timer: 1500,
        }
    ));
};
