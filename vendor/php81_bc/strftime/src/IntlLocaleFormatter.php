<?php

namespace PHP81_BC\strftime;

use DateTimeInterface;
use IntlDateFormatter;
use IntlGregorianCalendar;

/**
 * This formatter uses the IntlDateFormatter class to do proper, locale aware formatting
 */
class IntlLocaleFormatter extends AbstractLocaleFormatter
{

  /** @var string[] strftime to ICU placeholders */
  protected $formats = [
    '%a' => 'ccc',  // An abbreviated textual representation of the day	Sun through Sat
    '%A' => 'EEEE',  // A full textual representation of the day	Sunday through Saturday
    '%b' => 'LLL',  // Abbreviated month name, based on the locale	Jan through Dec
    '%B' => 'MMMM',  // Full month name, based on the locale	January through December
    '%h' => 'MMM',  // Abbreviated month name, based on the locale (an alias of %b)	Jan through Dec
  ];

  /** @inheritdoc */
  public function __invoke(DateTimeInterface $timestamp, string $format)
  {
    $tz = $timestamp->getTimezone();
    $date_type = IntlDateFormatter::FULL;
    $time_type = IntlDateFormatter::FULL;
    $pattern = '';

    switch ($format) {
      // %c = Preferred date and time stamp based on locale
      // Example: Tue Feb 5 00:45:10 2009 for February 5, 2009 at 12:45:10 AM
      case '%c':
        $date_type = IntlDateFormatter::LONG;
        $time_type = IntlDateFormatter::SHORT;
        break;

      // %x = Preferred date representation based on locale, without the time
      // Example: 02/05/09 for February 5, 2009
      case '%x':
        $date_type = IntlDateFormatter::SHORT;
        $time_type = IntlDateFormatter::NONE;
        break;

      // Localized time format
      case '%X':
        $date_type = IntlDateFormatter::NONE;
        $time_type = IntlDateFormatter::MEDIUM;
        break;

      default:
        if (!isset($this->formats[$format])) {
          throw new \RuntimeException("'$format' is not a supported locale placeholder");
        }
        $pattern = $this->formats[$format];
    }

    // In October 1582, the Gregorian calendar replaced the Julian in much of Europe, and
    //  the 4th October was followed by the 15th October.
    // ICU (including IntlDateFormattter) interprets and formats dates based on this cutover.
    // Posix (including strftime) and timelib (including DateTimeImmutable) instead use
    //  a "proleptic Gregorian calendar" - they pretend the Gregorian calendar has existed forever.
    // This leads to the same instants in time, as expressed in Unix time, having different representations
    //  in formatted strings.
    // To adjust for this, a custom calendar can be supplied with a cutover date arbitrarily far in the past.
    $calendar = IntlGregorianCalendar::createInstance();
    // NOTE: IntlGregorianCalendar::createInstance DOES NOT return an IntlGregorianCalendar instance when
    // using a non-Gregorian locale (e.g. fa_IR)! In that case, setGregorianChange will not exist.
    if (method_exists($calendar, 'setGregorianChange')) $calendar->setGregorianChange(PHP_INT_MIN);

    return (new IntlDateFormatter($this->locale, $date_type, $time_type, $tz, $calendar, $pattern))->format($timestamp);
  }

}
