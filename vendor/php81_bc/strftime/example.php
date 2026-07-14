<?php
  require __DIR__ . '/autoload.php';

  use function PHP81_BC\strftime;

  $date = '20220312';
  echo strftime('%Y-%m-%d %H:%M:%S'), PHP_EOL;
  echo strftime('%Y-%m-%d %H:%M:%S', $date), PHP_EOL;
  echo strftime('%Y-%m-%d %H:%M:%S', strtotime($date)), PHP_EOL;
