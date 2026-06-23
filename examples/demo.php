<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use SDOSA\Hijri;

$h = Hijri::toHijri('1982-12-02');
printf("Gregorian 1982-12-02 -> Hijri %s (%s)\n", $h->toIso(), $h->monthName());

$g = Hijri::toGregorian('1403-02-17');
printf("Hijri 1403-02-17 -> Gregorian %s (%s)\n", $g->toIso(), $g->dayName());

$today = Hijri::today();
printf("Today (Hijri): %s — %s %d %s\n", $today->toIso(), $today->monthName('ar'), $today->day(), $today->dayName('ar'));
