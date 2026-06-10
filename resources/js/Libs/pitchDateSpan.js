import moment from 'moment';

/**
 * Inclusive calendar-day span between pitch start and end (matches backend).
 */
export function inclusivePitchDaySpan(start, end) {
    if (! start || ! end) {
        return null;
    }

    return moment(end).startOf('day').diff(moment(start).startOf('day'), 'days') + 1;
}

/**
 * End date (YYYY-MM-DD) for a maximum inclusive span from start.
 */
export function maxPitchEndDate(start, maxDays) {
    return moment(start).startOf('day').add(maxDays - 1, 'days').format('YYYY-MM-DD');
}

export function pitchDateSpanExceedsMax(start, end, maxDays) {
    if (! maxDays) {
        return false;
    }

    const span = inclusivePitchDaySpan(start, end);

    return span !== null && span > maxDays;
}
