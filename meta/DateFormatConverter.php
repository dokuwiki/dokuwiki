<?php

namespace dokuwiki\plugin\struct\meta;

/**
 * Class DateFormatConverter
 *
 * Allows conversion between the two format strings used in PHP. Not all placeholders are available in both
 * formats. The conversion tries will use similar but not exactly the same placeholders if possible. When no suitable
 * replacement can be found, the placeholder is removed.
 *
 * Do not use this where formats are used in creating machine readable data (like feeds, APIs whatever). This is
 * only meant for cases where human read output is created.
 *
 * @package dokuwiki\plugin\struct\meta
 */
class DateFormatConverter {
    protected static $strftime = array(
        // Day
        '%a' => 'D', // An abbreviated textual representation of the day    Sun through Sat
        '%A' => 'l', // A full textual representation of the day    Sunday through Saturday
        '%d' => 'd', // Two-digit day of the month (with leading zeros)    01 to 31
        '%e' => 'j', // Day of the month, with a space preceding single digits. Not implemented as described on Windows. See below for more information.    1 to 31
        '%j' => '', // NOT SUPPORTED Day of the year, 3 digits with leading zeros    001 to 366
        '%u' => 'N', // ISO-8601 numeric representation of the day of the week    1 (for Monday) through 7 (for Sunday)
        '%w' => 'w', // Numeric representation of the day of the week    0 (for Sunday) through 6 (for Saturday)
        // Week
        '%U' => '', // NOT SUPPORTED Week number of the given year, starting with the first Sunday as the first week    13 (for the 13th full week of the year)
        '%V' => 'W', // ISO-8601:1988 week number of the given year, starting with the first week of the year with at least 4 weekdays, with Monday being the start of the week    01 through 53 (where 53 accounts for an overlapping week)
        '%W' => '', // NOT SUPPORTED A numeric representation of the week of the year, starting with the first Monday as the first week    46 (for the 46th week of the year beginning with a Monday)
        // Month
        '%b' => 'M', // Abbreviated month name, based on the locale    Jan through Dec
        '%B' => 'F', // Full month name, based on the locale    January through December
        '%h' => 'M', // Abbreviated month name, based on the locale (an alias of %b)    Jan through Dec
        '%m' => 'm', // Two digit representation of the month    01 (for January) through 12 (for December)
        // Year
        '%C' => '', // NOT SUPPORTED Two digit representation of the century (year divided by 100, truncated to an integer)    19 for the 20th Century
        '%g' => 'y', // Two digit representation of the year going by ISO-8601:1988 standards (see %V)    Example: 09 for the week of January 6, 2009
        '%G' => 'Y', // The full four-digit version of %g    Example: 2008 for the week of January 3, 2009
        '%y' => 'y', // Two digit representation of the year    Example: 09 for 2009, 79 for 1979
        '%Y' => 'Y', // Four digit representation for the year    Example: 2038
        // Time
        '%H' => 'H', // Two digit representation of the hour in 24-hour format    00 through 23
        '%k' => 'G', // Two digit representation of the hour in 24-hour format, with a space preceding single digits    0 through 23
        '%I' => 'h', // Two digit representation of the hour in 12-hour format    01 through 12
        '%l' => 'g', // (lower-case 'L') Hour in 12-hour format, with a space preceding single digits    1 through 12
        '%M' => 'i', // Two digit representation of the minute    00 through 59
        '%p' => 'A', // UPPER-CASE 'AM' or 'PM' based on the given time    Example: AM for 00:31, PM for 22:23
        '%P' => 'a', // lower-case 'am' or 'pm' based on the given time    Example: am for 00:31, pm for 22:23
        '%r' => 'h:i:s A', // Same as %I:%M:%S %p    Example: 09:34:17 PM for 21:34:17
        '%R' => 'H:i', // Same as %H:%M    Example: 00:35 for 12:35 AM, 16:44for 4:44 PM
        '%S' => 's', // Two digit representation of the second    00 through 59
        '%T' => 'H:i:s', // Same as %H:%M:%S    Example: 21:34:17 for 09:34:17 PM
        '%X' => 'H:i:s', // Preferred time representation based on locale, without the date    Example: 03:59:16 or 15:59:16
        '%z' => 'z', // The time zone offset. Not implemented as described on Windows. See below for more information.    Example: -0500 for US Eastern Time
        '%Z' => 'T', // The time zone abbreviation. Not implemented as described on Windows. See below for more information.    Example: EST for Eastern Time
        // Time and Date Stamps
        '%c' => 'D M j H:i:s Y', // Preferred date and time stamp based on locale    Example: Tue Feb 5 00:45:10 2009 for February 5, 2009 at 12:45:10 AM
        '%D' => 'm/d/y', // Same as %m/%d/%y    Example: 02/05/09 for February 5, 2009
        '%F' => 'Y/m/d', // Same as %Y-%m-%d (commonly used in database datestamps)    Example: 2009-02-05 for February 5, 2009
        '%s' => 'U', // Unix Epoch Time timestamp (same as the time() function)    Example: 305815200 for September 10, 1979 08:40:00 AM
        '%x' => 'm/d/y', // Preferred date representation based on locale, without the time    Example: 02/05/09 for February 5, 2009
        // Miscellaneous
        '%n' => "\n", // A newline character (\n)    ---
        '%t' => "\t", // A Tab character (\t)    ---
        '%%' => '%', // A literal percentage character (%)    ---
    );

    protected static $date = array(
        // Day
        'd' => '%d', // Day of the month, 2 digits with leading zeros    01 to 31
        'D' => '%a', // A textual representation of a day, three letters    Mon through Sun
        'j' => '%e', // Day of the month without leading zeros    1 to 31
        'l' => '%A', // (lowercase 'L') A full textual representation of the day of the week    Sunday through Saturday
        'N' => '%u', // ISO-8601 numeric representation of the day of the week (added in PHP 5.1.0)    1 (for Monday) through 7 (for Sunday)
        'S' => '', // NOT SUPPORTED English ordinal suffix for the day of the month, 2 characters    st, nd, rd or th. Works well with j
        'w' => '%w', // Numeric representation of the day of the week    0 (for Sunday) through 6 (for Saturday)
        'z' => '', // NOT SUPPORTED The day of the year (starting from 0)    0 through 365
        // Week
        'W' => '%V', // ISO-8601 week number of year, weeks starting on Monday (added in PHP 4.1.0)    Example: 42 (the 42nd week in the year)
        // Month
        'F' => '%B', // A full textual representation of a month, such as January or March    January through December
        'm' => '%m', // Numeric representation of a month, with leading zeros    01 through 12
        'M' => '%b', // A short textual representation of a month, three letters    Jan through Dec
        'n' => '%m', // Numeric representation of a month, without leading zeros    1 through 12
        't' => '', // NOT SUPPORTED Number of days in the given month    28 through 31
        // Year
        'L' => '', // NOT SUPPORTED Whether it's a leap year    1 if it is a leap year, 0 otherwise.
        'o' => '%g', // ISO-8601 week-numbering year. This has the same value as Y, except that if the ISO week number (W) belongs to the previous or next year, that year is used instead. (added in PHP 5.1.0)    Examples: 1999or 2003
        'Y' => '%Y', // A full numeric representation of a year, 4 digits    Examples: 1999or 2003
        'y' => '%y', // A two digit representation of a year    Examples: 99 or03
        // Time
        'a' => '%P', // Lowercase Ante meridiem and Post meridiem    am or pm
        'A' => '%p', // Uppercase Ante meridiem and Post meridiem    AM or PM
        'B' => '', // NOT SUPPORTED Swatch Internet time    000 through 999
        'g' => '%l', // 12-hour format of an hour without leading zeros    1 through 12
        'G' => '%k', // 24-hour format of an hour without leading zeros    0 through 23
        'h' => '%I', // 12-hour format of an hour with leading zeros    01 through 12
        'H' => '%H', // 24-hour format of an hour with leading zeros    00 through 23
        'i' => '%M', // Minutes with leading zeros    00 to 59
        's' => '%S', // Seconds, with leading zeros    00 through 59
        'u' => '%s000000', // Microseconds (added in PHP 5.2.2). Note that date() will always generate000000 since it takes an integer parameter, whereas DateTime::format()does support microseconds if DateTime was created with microseconds.    Example: 654321
        // Timezone
        'e' => '%Z', // Timezone identifier (added in PHP 5.1.0)    Examples: UTC,GMT,Atlantic/Azores
        'I' => '', // NOT SUPPORTED (capital i) Whether or not the date is in daylight saving time    1 if Daylight Saving Time, 0otherwise.
        'O' => '%z', // Difference to Greenwich time (GMT) in hours    Example: +0200
        'P' => '%z', // Difference to Greenwich time (GMT) with colon between hours and minutes (added in PHP 5.1.3)    Example: +02:00
        'T' => '%Z', // Timezone abbreviation    Examples: EST,MDT ...
        'Z' => '', // NOT SUPPORTED Timezone offset in seconds. The offset for timezones west of UTC is always negative, and for those east of UTC is always positive.    -43200 through50400
        // Full Date/Time
        'c' => '', // NOT SUPPORTED ISO 8601 date (added in PHP 5)    2004-02-12T15:19:21+00:00
        'r' => '%a, %e %b %Y %H:%M:%S %s', // » RFC 2822 formatted date    Example: Thu, 21 Dec 2000 16:01:07 +0200
        'U' => '%s', // Seconds since the Unix Epoch (January 1 1970 00:00:00 GMT)    See also time()
    );

    /**
     * Convert a strftime format string to a date format string
     *
     * @param string $strftime
     * @return string
     */
    static public function toDate($strftime) {
        $date = $strftime;

        /* All characters that are not strftime placeholders need to be escaped */
        {
            $datekeys = array_keys(self::$date);
            // create negative lookbehind regex to match all known date chars that are not a strtime pattern now
            $from = array_map(
                function ($in) {
                    return '/(?<!%)' . $in . '/';
                },
                $datekeys
            );
            // those need to be escaped
            $to = array_map(
                function ($in) {
                    return '\\' . $in;
                },
                $datekeys
            );
            // escape date chars
            $date = preg_replace($from, $to, $date);
        }

        /* strftime to date conversion */
        {
            $date = str_replace(
                array_keys(self::$strftime),
                array_values(self::$strftime),
                $date
            );
        }

        return $date;
    }

    /**
     * Convert a date format string to a strftime format string
     *
     * @param string $date
     * @return string
     */
    static public function toStrftime($date) {
        /* date to strftime conversion */
        {
            // create negative lookbehind regex to match all unescaped known chars
            $from = array_keys(self::$date);
            $from = array_map(
                function ($in) {
                    return '/(?<!\\\\)' . $in . '/';
                },
                $from
            );
            $to = array_values(self::$date);

            // percents need escaping:
            array_unshift($from, '/%/');
            array_unshift($to, '%%');

            // replace all the placeholders
            $strftime = preg_replace($from, $to, $date);
        }

        /* unescape date escapes */
        {
            $datekeys = array_keys(self::$date);
            $from = array_map(
                function ($in) {
                    return '/\\\\' . $in . '/';
                },
                $datekeys
            );
            $strftime = preg_replace($from, $datekeys, $strftime);
        }

        return $strftime;
    }
}
