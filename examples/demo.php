<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use SDOSA\GregorianDate;
use SDOSA\Hijri;
use SDOSA\HijriDate;

// The one-liner entry point — accepts a string, DateTime, or Carbon.
$h = Hijri::toHijri('1982-12-02');
printf("Gregorian 1982-12-02 -> Hijri %s (%s)\n", $h->toIso(), $h->monthName());

$g = Hijri::toGregorian('1403-02-17');
printf("Hijri 1403-02-17 -> Gregorian %s (%s)\n", $g->toIso(), $g->dayName());

// Or build a value object yourself with ::make() and convert from there.
$ramadan = HijriDate::make(1445, 9, 1);
printf("Hijri %s -> Gregorian %s\n", $ramadan->toIso(), $ramadan->toGregorian()->toIso());
printf("Gregorian %s -> Hijri %s\n", '2024-03-11', GregorianDate::make(2024, 3, 11)->toHijri()->toIso());

$today = Hijri::today();
printf("Today (Hijri): %s — %s %d %s\n", $today->toIso(), $today->monthName('ar'), $today->day(), $today->dayName('ar'));
