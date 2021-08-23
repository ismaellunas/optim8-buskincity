import Swal from 'sweetalert2';
import { assign } from 'lodash';

const timer = 1800;
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
            timer: timer,
        }
    ));
};
