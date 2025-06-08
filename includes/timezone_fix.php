<?php
/**
 * Timezone Utility Functions
 * Handles timezone conversions and date operations for IST (+5:30)
 */

// Set default timezone for the application
date_default_timezone_set('Asia/Kolkata');

/**
 * Get current date in IST timezone
 * @return string Current date in Y-m-d format
 */
function getCurrentDateIST() {
    return date('Y-m-d');
}

/**
 * Get current datetime in IST timezone
 * @return string Current datetime in Y-m-d H:i:s format
 */
function getCurrentDateTimeIST() {
    return date('Y-m-d H:i:s');
}

/**
 * Convert UTC date to IST date
 * @param string $utcDate UTC date string
 * @return string IST date in Y-m-d format
 */
function convertUTCToIST($utcDate) {
    $utc = new DateTime($utcDate, new DateTimeZone('UTC'));
    $utc->setTimezone(new DateTimeZone('Asia/Kolkata'));
    return $utc->format('Y-m-d');
}

/**
 * Get attendance query date range to handle timezone storage issues
 * Returns both today and yesterday to catch timezone-shifted data
 * @return array Array with 'today' and 'yesterday' dates
 */
function getAttendanceDateRange() {
    return [
        'today' => date('Y-m-d'),
        'yesterday' => date('Y-m-d', strtotime('-1 day'))
    ];
}

/**
 * Format date for display in IST
 * @param string $date Date string to format
 * @param string $format Output format (default: 'F j, Y')
 * @return string Formatted date string
 */
function formatDateIST($date, $format = 'F j, Y') {
    return date($format, strtotime($date));
}

/**
 * Get MySQL timezone setting query
 * @return string SQL query to set MySQL timezone to IST
 */
function getMySQLTimezoneQuery() {
    return "SET time_zone = '+05:30'";
}
?> 