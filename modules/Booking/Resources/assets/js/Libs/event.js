import moment from 'moment';

export function durationDateTimeText(date, time, duration, unit) {
    if (! (date && time)) {
        return null;
    }

    const startTime = moment(
        moment(date).format('YYYY-MM-DD') + ' ' + time
    );

    const endTime = moment(startTime).add(duration, unit);

    return (
        startTime.format('k:mm')
        + ' - ' + endTime.format('k:mm')
        + ', ' + startTime.format('D MMMM YYYY')
    );
};
