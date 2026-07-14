<?php

namespace PHP81_BC\strftime;

use DateTimeInterface;

abstract class AbstractLocaleFormatter {

  protected $locale;

  /**
   * Constructor
   *
   * @param string $locale The locale to use when formatting
   */
  public function __construct($locale)
  {
    $this->locale = $locale;
  }

  /**
   * Format the given local dependent placeholder
   *
   * @param DateTimeInterface $timestamp
   * @param string $format The strftime compatible, locale dependend placeholder
   * @throws \RuntimeException when the given place holder is unknown
   * @return false|string
   */
  abstract public function __invoke(DateTimeInterface $timestamp, string $format);

}
