<?php
  namespace PHP81_BC;

  use DateTime;
  use DateTimeZone;
  use DateTimeInterface;
  use Exception;
  use InvalidArgumentException;
  use Locale;
  use PHP81_BC\strftime\DateLocaleFormatter;
  use PHP81_BC\strftime\IntlLocaleFormatter;

  /**
   * Locale-formatted strftime using IntlDateFormatter (PHP 8.1 compatible)
   * This provides a cross-platform alternative to strftime() for when it will be removed from PHP.
   * Note that output can be slightly different between libc sprintf and this function as it is using ICU.
   *
   * Usage:
   * use function \PHP81_BC\strftime;
   * echo strftime('%A %e %B %Y %X', new \DateTime('2021-09-28 00:00:00'), 'fr_FR');
   *
   * Original use:
   * \setlocale(LC_TIME, 'fr_FR.UTF-8');
   * echo \strftime('%A %e %B %Y %X', strtotime('2021-09-28 00:00:00'));
   *
   * @param  string $format Date format
   * @param  integer|string|DateTime $timestamp Timestamp
   * @param  string|null $locale locale
   * @return string
   * @throws InvalidArgumentException
   * @author BohwaZ <https://bohwaz.net/>
   */
  function strftime (string $format, $timestamp = null, ?string $locale = null) : string {
    if (!($timestamp instanceof DateTimeInterface)) {
      $timestamp = is_int($timestamp) ? '@' . $timestamp : (string) $timestamp;

      try {
        $timestamp = new DateTime($timestamp);
      } catch (Exception $e) {
        throw new InvalidArgumentException('$timestamp argument is neither a valid UNIX timestamp, a valid date-time string or a DateTime object.', 0, $e);
      }

      $timestamp->setTimezone(new DateTimeZone(date_default_timezone_get()));
    }

    if (class_exists('\\IntlDateFormatter') && !isset($_SERVER['STRFTIME_NO_INTL'])) {
      $locale = Locale::canonicalize($locale ?? (Locale::getDefault() ?? setlocale(LC_TIME, '0')));
      $locale_formatter = new IntlLocaleFormatter($locale);
    } else {
      $locale_formatter = new DateLocaleFormatter($locale);
    }

    // Same order as https://www.php.net/manual/en/function.strftime.php
    $translation_table = [
      // Day
      '%a' => $locale_formatter,
      '%A' => $locale_formatter,
      '%d' => 'd',
      '%e' => function ($timestamp) {
        return sprintf('% 2u', $timestamp->format('j'));
      },
      '%j' => function ($timestamp) {
        // Day number in year, 001 to 366
        return sprintf('%03d', $timestamp->format('z')+1);
      },
      '%u' => 'N',
      '%w' => 'w',

      // Week
      '%U' => function ($timestamp) {
        // Number of weeks between date and first Sunday of year
        $day = new DateTime(sprintf('%d-01 Sunday', $timestamp->format('Y')));
        return sprintf('%02u', 1 + ($timestamp->format('z') - $day->format('z')) / 7);
      },
      '%V' => 'W',
      '%W' => function ($timestamp) {
        // Number of weeks between date and first Monday of year
        $day = new DateTime(sprintf('%d-01 Monday', $timestamp->format('Y')));
        return sprintf('%02u', 1 + ($timestamp->format('z') - $day->format('z')) / 7);
      },

      // Month
      '%b' => $locale_formatter,
      '%B' => $locale_formatter,
      '%h' => $locale_formatter,
      '%m' => 'm',

      // Year
      '%C' => function ($timestamp) {
        // Century (-1): 19 for 20th century
        return floor($timestamp->format('Y') / 100);
      },
      '%g' => function ($timestamp) {
        return substr($timestamp->format('o'), -2);
      },
      '%G' => 'o',
      '%y' => 'y',
      '%Y' => 'Y',

      // Time
      '%H' => 'H',
      '%k' => function ($timestamp) {
        return sprintf('% 2u', $timestamp->format('G'));
      },
      '%I' => 'h',
      '%l' => function ($timestamp) {
        return sprintf('% 2u', $timestamp->format('g'));
      },
      '%M' => 'i',
      '%p' => 'A', // AM PM (this is reversed on purpose!)
      '%P' => 'a', // am pm
      '%r' => 'h:i:s A', // %I:%M:%S %p
      '%R' => 'H:i', // %H:%M
      '%S' => 's',
      '%T' => 'H:i:s', // %H:%M:%S
      '%X' => $locale_formatter, // Preferred time representation based on locale, without the date

      // Timezone
      '%z' => 'O',
      '%Z' => 'T',

      // Time and Date Stamps
      '%c' => $locale_formatter,
      '%D' => 'm/d/Y',
      '%F' => 'Y-m-d',
      '%s' => 'U',
      '%x' => $locale_formatter,
    ];

    $out = preg_replace_callback('/(?<!%)%([_#-]?)([a-zA-Z])/', function ($match) use ($translation_table, $timestamp) {
      $prefix = $match[1];
      $char = $match[2];
      $pattern = '%'.$char;
      if ($pattern == '%n') {
        return "\n";
      } elseif ($pattern == '%t') {
        return "\t";
      }

      if (!isset($translation_table[$pattern])) {
        throw new InvalidArgumentException(sprintf('Format "%s" is unknown in time format', $pattern));
      }

      $replace = $translation_table[$pattern];

      if (is_string($replace)) {
        $result = $timestamp->format($replace);
      } else {
        $result = $replace($timestamp, $pattern);
      }

      switch ($prefix) {
        case '_':
          // replace leading zeros with spaces but keep last char if also zero
          return preg_replace('/\G0(?=.)/', ' ', $result);
        case '#':
        case '-':
          // remove leading zeros but keep last char if also zero
          return preg_replace('/^[0\s]+(?=.)/', '', $result);
      }

      return $result;
    }, $format);

    $out = str_replace('%%', '%', $out);
    return $out;
  }
