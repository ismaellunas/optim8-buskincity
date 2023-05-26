import axios from 'axios';

export const maxFileSize = async () => {
    return await getValueByKey('max_file_size');
};

async function getValueByKey(key) {
    let response = null;

    try {
        response = await axios.get(
            `/admin/api/setting/${key}/key`
        );
    } catch (e) {
        throw new Error(e.message);
    }

    return response?.data ?? null;
}