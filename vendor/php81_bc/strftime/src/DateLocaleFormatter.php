<?php

namespace PHP81_BC\strftime;

use DateTimeInterface;

/**
 * This formatter uses simple, non-locale aware formatting of dates
 *
 * It should only be used when the intl extension is not available and thus the IntlLocaleFormatter can't be used
 */
class DateLocaleFormatter extends AbstractLocaleFormatter
{
  /** @var string[] strftime() to date() like formats that are dependend on Locales */
  protected $formats = [
    '%a' => 'D',  // An abbreviated textual representation of the day	Sun through Sat
    '%A' => 'l',  // A full textual representation of the day	Sunday through Saturday
    '%b' => 'M',  // Abbreviated month name, 	Jan through Dec
    '%B' => 'F',  // Full month name, 	January through December
    '%h' => 'M',  // Abbreviated month name, (an alias of %b)	Jan through Dec
    '%c' => 'F j, Y \a\t g:i A', // Preferred date and time stamp
    '%x' => 'n/j/y', // Preferred date representation, without the time
    '%X' => 'g:i:s A', // Time only
  ];

  /** @inheritdoc */
  public function __invoke(DateTimeInterface $timestamp, string $format)
  {
    if (!isset($this->formats[$format])) {
      throw new \RuntimeException("'$format' is not a supported locale placeholder");
    }

    trigger_error('Formatting without \\IntlDateFormatter only return english formats');

    return $timestamp->format($this->formats[$format]);
  }

}
