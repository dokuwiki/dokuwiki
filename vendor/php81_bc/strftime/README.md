[![GitHub Workflow](https://github.com/alphp/strftime/actions/workflows/php.yml/badge.svg)](https://github.com/alphp/strftime/actions/workflows/php.yml)
[![GitHub license](https://img.shields.io/github/license/alphp/strftime)](https://github.com/alphp/strftime/blob/master/LICENSE)
[![GitHub release](https://img.shields.io/github/release/alphp/strftime)](https://github.com/alphp/strftime/releases)
[![Packagist](https://img.shields.io/packagist/v/php81_bc/strftime)](https://packagist.org/packages/php81_bc/strftime)
[![Packagist Downloads](https://img.shields.io/packagist/dt/php81_bc/strftime)](https://packagist.org/packages/php81_bc/strftime/stats)
[![GitHub issues](https://img.shields.io/github/issues/alphp/strftime)](https://github.com/alphp/strftime/issues)
[![GitHub forks](https://img.shields.io/github/forks/alphp/strftime)](https://github.com/alphp/strftime/network)
[![GitHub stars](https://img.shields.io/github/stars/alphp/strftime)](https://github.com/alphp/strftime/stargazers)

# strftime
Locale-formatted strftime using IntlDateFormatter (PHP 8.1 compatible)

This provides a cross-platform alternative to strftime() for when it will be removed from PHP.

Note that output can be slightly different between libc sprintf and this function as it is using ICU.

Original code: https://gist.github.com/bohwaz/42fc223031e2b2dd2585aab159a20f30

Original autor: [BohwaZ](https://bohwaz.net/)

# Table of contents
- [Requirements](#requirements)
- [Installation](#installation)
  - [Composer install](#composer-install)
  - [Manual install](#manual-install)
- [Usage](#usage)
  - [Original use](#original-use)
- [Formats](#formats)
  - [Day](#day)
  - [Week](#week)
  - [Month](#month)
  - [Year](#year)
  - [Time](#time)
  - [Time and Date Stamps](#time-and-date-stamps)
  - [Miscellaneous](#miscellaneous)

## Requirements
- PHP >= 7.1.0
- ext-intl ([Internationalization extension ICU](https://www.php.net/manual/en/book.intl.php))

[TOC](#table-of-contents)

## Installation

### Composer install
You can install this plugin into your application using [composer](https://getcomposer.org):

- Add php81_bc/strftime package to your project:
  ```bash
    composer require php81_bc/strftime
  ```

- Load the function PHP81_BC\strftime in your project
  ```php
  <?php
    require 'vendor/autoload.php';
    use function PHP81_BC\strftime;
  ```

[TOC](#table-of-contents)

### Manual install
- Download [php-8.1-strftime.php](https://github.com/alphp/strftime/raw/master/src/php-8.1-strftime.php) and save it to an accessible path of your project.
- Load the function PHP81_BC\strftime in your project
  ```php
  <?php
    require 'php-8.1-strftime.php';
    use function PHP81_BC\strftime;
  ```

[TOC](#table-of-contents)

## Usage
```php
  use function PHP81_BC\strftime;
  echo strftime('%A %e %B %Y %X', new \DateTime('2021-09-28 00:00:00'), 'fr_FR');
```

[TOC](#table-of-contents)

### Original use
```php
  \setlocale(LC_TIME, 'fr_FR.UTF-8');
  echo \strftime('%A %e %B %Y %X', strtotime('2021-09-28 00:00:00'));
```

[TOC](#table-of-contents)

## Formats

### Day
| Format | Description                                            | Example returned values                         |
|:------:|--------------------------------------------------------|-------------------------------------------------|
|  `%a`  | An abbreviated textual representation of the day       | `Sun` through `Sat`                             |
|  `%A`  | A full textual representation of the day               | `Sunday` through `Saturday`                     |
|  `%d`  | Two-digit day of the month (with leading zeros)        | `01` to `31`                                    |
|  `%e`  | Day of the month, with a space preceding single digits | `' 1'` to `'31'`                                    |
|  `%j`  | Day of the year, 3 digits with leading zeros           | `001` to `366`                                  |
|  `%u`  | ISO-8601 numeric representation of the day of the week | `1` (for `Monday`) through `7` (for `Sunday`)   |
|  `%w`  | Numeric representation of the day of the week          | `0` (for `Sunday`) through `6` (for `Saturday`) |

[TOC](#table-of-contents)
### Week
| Format | Description                                                                                                                                            | Example returned values                                        |
|:------:|--------------------------------------------------------------------------------------------------------------------------------------------------------|----------------------------------------------------------------|
|  `%U`  | Week number of the given year, starting with the first Sunday as the first week                                                                        | `13` (for the `13th full week of the year`)                    |
|  `%V`  | ISO-8601:1988 week number of the given year, starting withthe first week of the year with at least 4 weekdays, with Monday being the start of the week | `01` through `53` (where 53 accounts for an overlapping week)  |
|  `%W`  | A numeric representation of the week of the year, starting with the first Monday as the first week                                                     | `46` (for the `46th week of the year` beginning with a Monday) |

**NOTE**: All week formats are two-digit, with leading zeros.

[TOC](#table-of-contents)
### Month
| Format | Description                                                    | Example returned values                            |
|:------:|----------------------------------------------------------------|----------------------------------------------------|
|  `%b`  | Abbreviated month name, based on the locale                    | `Jan` through `Dec`                                |
|  `%B`  | Full month name, based on the locale                           | `January` through `December`                       |
|  `%h`  | Abbreviated month name, based on the locale (an alias of `%b`) | `Jan` through `Dec`                                |
|  `%m`  | Two digit representation of the month                          | `01` (for `January`) through `12` (for `December`) |

[TOC](#table-of-contents)
### Year
| Format | Description                                                                            | Example returned values                         |
|:------:|----------------------------------------------------------------------------------------|-------------------------------------------------|
|  `%C`  | Two digit representation of the century (year divided by 100, truncated to an integer) | `19` for the `20th Century`                     |
|  `%g`  | Two digit representation of the year going by ISO-8601:1988 standards (see `%V`)       | Example: `09` for the week of `January 6, 2009` |
|  `%G`  | The full four-digit version of `%g`                                                    | Example: `2009` for the `January 3, 2009`       |
|  `%y`  | Two digit representation of the year                                                   | Example: `09` for `2009`, `79` for `1979`       |
|  `%Y`  | Four digit representation for the year                                                 | Example: `2038`                                 |

[TOC](#table-of-contents)
### Time
| Format | Description                                                                   | Example returned values                                |
|:------:|-------------------------------------------------------------------------------|--------------------------------------------------------|
|  `%H`  | Two digit representation of the hour in 24-hour format                        | `00` through `23`                                      |
|  `%k`  | Hour in 24-hour format, with a space preceding single digits                  | `' 0'` through `'23'`                                      |
|  `%I`  | Two digit representation of the hour in 12-hour format                        | `01` through `12`                                      |
|  `%l`  | (lower-case 'L') Hour in 12-hour format, with a space preceding single digits | `' 1'` through `'12'`                                      |
|  `%M`  | Two digit representation of the minute                                        | `00` through `59`                                      |
|  `%p`  | UPPER-CASE '`AM`' or '`PM`' based on the given time                           | Example: `AM` for `00:31`, `PM` for `22:23`            |
|  `%P`  | lower-case '`am`' or '`pm`' based on the given time                           | Example: `am` for `00:31`, `pm` for `22:23`            |
|  `%r`  | Same as "`%I:%M:%S %p`"                                                       | Example: `09:34:17 PM` for `21:34:17`                  |
|  `%R`  | Same as "`%H:%M`"                                                             | Example: `00:35` for `12:35 AM`, `16:44` for `4:44 PM` |
|  `%S`  | Two digit representation of the second                                        | `00` through `59`                                      |
|  `%T`  | Same as "`%H:%M:%S`"                                                          | Example: `21:34:17` for `09:34:17 PM`                  |
|  `%X`  | Preferred time representation based on locale, without the date               | Example: `03:59:16` or `15:59:16`                      |
|  `%z`  | The time zone offset                                                          | Example: `-0500` for `US Eastern Time`                 |
|  `%Z`  | The time zone abbreviation                                                    | Example: `EST` for `Eastern Time`                      |

[TOC](#table-of-contents)
### Time and Date Stamps
| Format | Description                                                     | Example returned values                                                  |
|:------:|-----------------------------------------------------------------|--------------------------------------------------------------------------|
|  `%c`  | Preferred date and time stamp based on locale                   | Example: `Tue Feb 5 00:45:10 2009` for `February 5, 2009 at 12:45:10 AM` |
|  `%D`  | Same as "`%m/%d/%y`"                                            | Example: `02/05/09` for `February 5, 2009`                               |
|  `%F`  | Same as "`%Y-%m-%d`" (commonly used in database datestamps)     | Example: `2009-02-05` for `February 5, 2009`                             |
|  `%s`  | Unix Epoch Time timestamp (same as the `time()` function)       | Example: `305815200` for `September 10, 1979 08:40:00 AM`                |
|  `%x`  | Preferred date representation based on locale, without the time | Example: `02/05/09` for `February 5, 2009`                               |

[TOC](#table-of-contents)
### Miscellaneous
| Format | Description                          |
|:------:|--------------------------------------|
|  `%n`  | A newline character ("\n")           |
|  `%t`  | A Tab character ("\t")               |
|  `%%`  | A literal percentage character ("%") |

[TOC](#table-of-contents)
