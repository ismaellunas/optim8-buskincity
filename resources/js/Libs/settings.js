import axios from 'axios';

export const maxFileSize = async () => {
    let response = null;

    try {
        response = await axios.get(
            '/admin/api/setting/max-file-size'
        );
    } catch (e) {
        throw new Error(e.message);
    }

    return response?.data ?? null;
};