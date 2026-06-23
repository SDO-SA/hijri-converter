<?php

declare(strict_types=1);

use SDOSA\Exceptions\DateOutOfRange;
use SDOSA\Exceptions\InvalidGregorianDate;
use SDOSA\Exceptions\InvalidHijriDate;
use SDOSA\GregorianDate;
use SDOSA\HijriDate;

it('accepts the exact range boundaries', function () {
    expect(new GregorianDate(1924, 8, 1))->toBeInstanceOf(GregorianDate::class)
        ->and(new GregorianDate(2077, 11, 16))->toBeInstanceOf(GregorianDate::class)
        ->and(new HijriDate(1343, 1, 1))->toBeInstanceOf(HijriDate::class)
        ->and(new HijriDate(1500, 12, 30))->toBeInstanceOf(HijriDate::class);
});

it('rejects Gregorian dates past the range', function () {
    new GregorianDate(2077, 11, 17);
})->throws(DateOutOfRange::class);

it('rejects Gregorian dates before the range', function () {
    new GregorianDate(1924, 7, 31);
})->throws(DateOutOfRange::class);

it('rejects Hijri years past the range', function () {
    new HijriDate(1501, 1, 1);
})->throws(DateOutOfRange::class);

it('rejects an impossible Hijri day-of-month', function () {
    $h = new HijriDate(1445, 1, 1);
    if ($h->monthLength() === 30) {
        $this->markTestSkipped('month 1 of 1445 has 30 days');
    }
    new HijriDate(1445, 1, 30);
})->throws(InvalidHijriDate::class);

it('rejects an impossible Gregorian day-of-month', function () {
    new GregorianDate(2023, 2, 30);
})->throws(InvalidGregorianDate::class);

it('rejects a bad month', function () {
    new GregorianDate(2000, 13, 1);
})->throws(InvalidGregorianDate::class);

it('rejects unparseable strings', function () {
    HijriDate::fromString('not-a-date');
})->throws(InvalidHijriDate::class);
