# Time Restrictions Implementation Summary

## Overview
Enhanced the event creation modal to restrict both **dates** and **times** based on the pitch schedule, ensuring events can only be created within the pitch's available hours.

## Changes Made

### 1. Backend Changes

#### `modules/Booking/Services/ProductEventService.php`
- **Enhanced `pitchScheduleInfo()` method** to return:
  - `startDate` / `endDate`: Actual date values for date range restrictions
  - `availableDaysArray`: Array of available day numbers (1-7, where 1=Monday, 7=Sunday)
  - `weeklyHoursData`: Detailed time ranges for each available day with structure:
    ```php
    [
        1 => [  // Monday
            ['started_time' => '09:00', 'ended_time' => '17:00'],
            ['started_time' => '18:00', 'ended_time' => '21:00']
        ],
        // ... other days
    ]
    ```

#### `modules/Booking/Services/ProductEventCrudService.php`
- **Enhanced `validateEventWithinPitchSchedule()` method** to validate both dates and times
- **Added `validateWeeklyHoursAgainstPitch()` method** that:
  - Checks if selected days are available in pitch schedule
  - Validates that event time ranges fall within pitch time ranges
  - Returns detailed validation errors with specific time constraints

### 2. Frontend Component Changes

#### `resources/js/Biz/Datepicker.vue` & `resources/js/Biz/DateTime.vue`
- Added `inheritAttrs: false` to prevent attribute fallthrough issues
- Added `v-bind="$attrs"` to pass through date/time picker properties

#### `modules/Booking/Resources/assets/js/Pages/ProductEventFormModal.vue`

**Date Restrictions:**
- Added computed properties:
  - `pitchMinDate`: Restricts dates to pitch start date or later
  - `pitchMaxDate`: Restricts dates to pitch end date or earlier
  - `disabledWeekDays`: Disables days not available in pitch schedule
  - `hasPitchWeeklyHours`: Checks if pitch has weekly hours configured

**Visual Guidance:**
- Added "Pitch Schedule Constraints" notification box showing:
  - Available date range
  - Available days of the week
  - Typical hours
- Added "Pitch Schedule Hours" section displaying detailed time ranges per day
- Updated prop definition to include new fields: `startDate`, `endDate`, `availableDaysArray`, `weeklyHoursData`

**Methods:**
- Added `getPitchTimeRangesForDay(dayIndex)`: Returns pitch time ranges for a specific day

#### `modules/Booking/Resources/assets/js/Pages/ScheduleRuleTimes.vue`
- Added `pitchTimeRanges` prop to receive pitch schedule constraints
- Added `isTimeOutsidePitchHours(timeRange)` method that:
  - Checks if event time range falls within any pitch time range
  - Returns `true` if outside bounds (triggers warning)
- Added warning notification when time is outside pitch hours
- Passes `pitchTimeRanges` down to `ScheduleRuleTime` component

#### `modules/Booking/Resources/assets/js/Pages/ScheduleRuleTime.vue`
- Added `pitchTimeRanges` prop
- Added computed properties:
  - `pitchMinTime`: Earliest allowed start time from pitch schedule
  - `pitchMaxTime`: Latest allowed end time from pitch schedule
- Applied `min-time` and `max-time` restrictions to time picker

#### `modules/Booking/Resources/assets/js/Pages/ProductEventList.vue` & `modules/Booking/Resources/assets/js/Pages/ProductEdit.vue`
- Updated `pitchSchedule` prop definition to include new fields

## How It Works

### Date Restrictions
1. Date picker only allows dates between `pitch_started_at` and `pitch_ended_at`
2. Days of the week not available in pitch schedule are disabled
3. Users see visual indicators of available dates

### Time Restrictions
1. **Time Picker Constraints**: Min/max time limits based on pitch schedule
2. **Visual Warnings**: Yellow warning boxes appear when times are outside pitch hours
3. **Backend Validation**: Server-side validation ensures data integrity
   - Validates day availability
   - Validates time ranges are within pitch time ranges
   - Returns specific error messages with allowed time ranges

### User Experience Flow
1. User clicks "Add New Event"
2. Modal shows pitch constraints (dates, days, times)
3. Date picker is restricted to valid date range
4. Unavailable weekdays are disabled
5. For each day selected:
   - User sees pitch hours for that day
   - Time picker is restricted to pitch min/max times
   - Warning appears if time is outside pitch hours
6. On submit:
   - Client-side validation prevents invalid submissions
   - Server-side validation catches any attempts to bypass restrictions

## Testing

Navigate to: `http://localhost/admin/booking/products/241/edit`

1. **Test Date Restrictions:**
   - Click "Add New Event"
   - Try selecting dates outside pitch range (should be disabled)
   - Try selecting unavailable weekdays (should be disabled)

2. **Test Time Restrictions:**
   - Enable a weekday in Weekly Hours section
   - Try setting times outside pitch hours
   - Warning notification should appear
   - Check that time picker respects min/max constraints

3. **Test Backend Validation:**
   - Use browser dev tools to bypass client restrictions
   - Submit form with invalid times
   - Should receive validation error from server

## Validation Error Format

When times are invalid, users receive specific errors:
```
Time range must be within Pitch hours: 09:00-17:00, 18:00-21:00
```

## Timezone Handling

### Issue
Date validation and date picker restrictions were failing due to timezone conversion:
- Backend dates stored as "2026-02-22" 
- JavaScript `new Date("2026-02-22")` interprets as UTC midnight
- Conversion to local timezone shifts the date (e.g., PST makes it Feb 21)
- Result: Feb 22 not selectable even when it's the valid end date

### Fix Applied

**Backend (`ProductEventCrudService.php`):**
- Parse all dates in the **pitch timezone** for consistent comparison
- Use `Carbon::parse($date, $timezone)->startOfDay()` to normalize
- Prevents date shifting during validation

**Frontend (`ProductEventFormModal.vue`):**
- Parse date strings as **local dates** without timezone conversion
- Use `new Date(year, month - 1, day)` instead of `new Date(dateString)`
- Ensures date picker min/max dates match exactly what's stored

## Benefits
- ✅ Prevents scheduling conflicts
- ✅ Ensures events respect pitch availability
- ✅ Clear visual feedback for users
- ✅ Multi-layer validation (client + server)
- ✅ Specific error messages guide users to fix issues
- ✅ Timezone-aware date handling prevents edge cases
- ✅ Date picker shows correct available range
